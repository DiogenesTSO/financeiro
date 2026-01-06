<?php

namespace App\Filament\Resources\ContasFuturasResource\RelationManagers;

use App\Models\ParcelaContaFutura;
use App\Models\Transacao;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ParcelasContasFuturasRelationManager extends RelationManager
{
    protected static string $relationship = 'parcelas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Parcelas')
            ->header(function () {
                $ownerRecord = $this->getOwnerRecord();
                $totalPago = $ownerRecord->parcelas->sum('valor_pago');
                $saldoDevedor = $ownerRecord->valor_total - $totalPago;
                return view('filament.components.parcelas-summary', [
                    'totalPago'     => $totalPago,
                    'saldoDevedor'  => $saldoDevedor,
                ]);
            })
            ->columns([
                TextColumn::make('qtd_parcelas')
                    ->alignCenter()
                    ->label('Parcelas'),
                TextColumn::make('valor')
                    ->label('Valor')
                    ->alignCenter()
                    ->money('BRL'),
                TextColumn::make('vencimento')
                    ->alignCenter()
                    ->date('d/m/Y'),
                IconColumn::make('is_pad')
                    ->boolean()
                    ->alignCenter()
                    ->icon(fn (bool $state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->label('Pago'),
                TextColumn::make('valor_pago')
                    ->label('Valor pago')
                    ->placeholder('Não informado')
                    ->alignCenter()
                    ->money('BRL'),
                TextColumn::make('pago_em')
                    ->date('d/m/Y')
                    ->placeholder('Não informado')
                    ->alignCenter()
                    ->label('Pago em'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Action::make('pagar')
                    ->label('Pagar parcela')
                    ->fillForm(fn (ParcelaContaFutura $record) => [
                        'valor_pago' => (float) $record->valor,
                        'pago_em'    => now(),
                    ])
                    ->form([
                        DatePicker::make('pago_em')->required()->label('data do pagamento'),
                        TextInput::make('valor_pago')
                            ->required()
                            ->label('Valor pago')
                            ->numeric()
                            ->minValue(0.01),
                    ])
                    ->action(function (array $data, ParcelaContaFutura $record) {
                        DB::transaction(function () use ($data, $record) {
                            $record->update([
                                'is_pad'        => true,
                                'pago_em'       => $data['pago_em'],
                                'valor_pago'    => $data['valor_pago']
                            ]);

                            $contaFutura = $record->contaFutura;
                            $conta       = $contaFutura->conta;

                            Transacao::create([
                                'familia_id'    => $contaFutura->familia_id,
                                'conta_id'      => $conta->id,
                                'categoria_id'  => $contaFutura->categoria_id,
                                'descricao'     => $contaFutura->descricao . ' - Parcela ' . $record->qtd_parcelas,
                                'valor'         => $data['valor_pago'],
                                'tipo'          => $contaFutura->tipo,
                                'data'          => $data['pago_em'],
                                'is_paid'       => true,
                                // 'conta_futura_id'   => $contaFutura->id,
                                // 'parcela_id'        => $record->id,
                            ]);

                            if ($contaFutura->tipo === 'despesa') {
                                $conta->decrement('saldo_atual', $data['valor_pago']);
                            } else {
                                $conta->increment('saldo_atual', $data['valor_pago']);
                            }

                            $this->recalcularParcelasRestantes($record);

                            $todasPagas = $contaFutura->parcelas()
                                ->where('is_pad', false)
                                ->doesntExist();
                            
                            if ($todasPagas) {
                                $contaFutura->update(['status' => 'concluido']);
                            }
                        });

                    
                    })
                    ->requiresConfirmation()
                    ->visible(fn (ParcelaContaFutura $record) => !$record->is_pad),
                Action::make('marcar_como_paga')
                    ->label('Ja paga?')
                    ->color('warning')
                    ->icon('heroicon-o-check')
                    ->fillForm(fn (ParcelaContaFutura $record) => [
                        'valor_pago' => (float) $record->valor,
                        'pago_em'    => now(),
                    ])
                    ->form([
                        DatePicker::make('pago_em')
                            ->required()
                            ->label('Data do pagamento')
                            ->default(now()),
                        TextInput::make('valor_pago')
                            ->required()
                            ->label('Valor pago')
                            ->numeric()
                            ->minValue(0.01),
                    ])
                    ->action(function (array $data, ParcelaContafutura $record) {
                        DB::transaction(function () use ($data, $record) {
                            $record->update([
                                'is_pad'        => true,
                                'pago_em'       => $data['pago_em'],
                                'valor_pago'    => $data['valor_pago']
                            ]);

                            $contaFutura = $record->contaFutura;

                            $todasPagas = $contaFutura->parcelas()
                                ->where('is_pad', false)
                                ->doesntExist();

                            if ($todasPagas) {
                                $contaFutura->update(['status' => 'concluido']);
                            }
                        });
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Marcar parcela como paga')
                    ->modalDescription('Esta ação NÃO criará transação nem alterará o saldo da conta.')
                    ->visible(fn (ParcelaContaFutura $record) => !$record->is_pad),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function recalcularParcelasRestantes(ParcelaContaFutura $parcelaPaga)
    {
        $conta = $parcelaPaga->contaFutura;
        $parcelasRestantes = $conta->parcelas()
            ->where('is_pad', false)
            ->orderBy('qtd_parcelas')
            ->get();

        if ($parcelasRestantes->isEmpty()) {
            return;
        }

        $valorExtra = $parcelaPaga->valor_pago - $parcelaPaga->valor;

        if ($valorExtra <= 0) {
            return;
        }

        $novoValor = $parcelasRestantes->sum('valor') - $valorExtra;
        $novaParcela = round($novoValor / $parcelasRestantes->count(), 2);

        foreach ($parcelasRestantes as $parcela) {
            $parcela->update(['valor' => $novaParcela]);
        }
    }
}

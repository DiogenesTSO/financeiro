<?php

namespace App\Filament\Widgets;

use App\Models\ParcelaContaFutura;
use App\Models\Transacao;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListaParcelas extends BaseWidget
{
    protected static ?string $heading = '📅 Parcelas a vencer no mês';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns($this->getColumns())
            ->actions($this->getActions())
            ->defaultSort('vencimento')
            ->paginated(false);
    }

    protected function getQuery(): Builder
    {
        return ParcelaContaFutura::query()
            ->where('is_pad', false)
            ->whereBetween('vencimento', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->whereHas('contaFutura', function ($query) {
                $query->where('familia_id', filament()->auth()->user()->familia_id);
            });
    }

    protected function getColumns(): array
    {
        return [
            TextColumn::make('vencimento')
                ->label('Vencimento')
                ->date('d/m/Y'),
            TextColumn::make('contaFutura.descricao')
                ->label('Descrição')
                ->alignCenter(),
            TextColumn::make('contaFutura.conta.nome')
                ->label('Nome da conta')
                ->alignCenter()
                ->badge(),
            TextColumn::make('valor')
                ->label('Valor')
                ->alignCenter()
                ->money('BRL'),
            TextColumn::make('qtd_parcelas')
                ->label('Parcelas')
                ->alignEnd()
                ->formatStateUsing(fn ($state, $record) => 
                    "{$state}/{$record->contaFutura->qtd_parcelas}"),
        ];
    }

    protected function getActions(): array
    {
        return [
            Action::make('pagar')
                ->label('Pagar conta')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->fillForm(fn (ParcelaContaFutura $record) => [
                    'pago_em' => now(),
                    'valor_pago' => $record->valor,
                ])
                ->form([
                    DatePicker::make('pago_em')
                        ->label('Data do pagamento')
                        ->required(),
                    TextInput::make('valor_pago')
                        ->label('Valor a ser pago')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
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
                            'descricao'     => "{$contaFutura->descricao} - Parcela {$record->numero_parcela}",
                            'valor'         => $data['valor_pago'],
                            'tipo'          => $contaFutura->tipo,
                            'data'          => $data['pago_em'],
                            'is_paid'       => true,
                        ]);

                        if ($contaFutura->tipo === 'despesa') {
                            $conta->decrement('saldo_atual', $data['valor_pago']);
                        } else {
                            $conta->increment('saldo_atual', $data['valor_pago']);
                        }

                        $todasPagas = $contaFutura->parcelas()
                            ->where('is_paid', false)
                            ->doesntExist();

                        if ($todasPagas) {
                            $contaFutura->update(['status', 'concluido']);
                        }
                    });
                })
                ->requiresConfirmation()
                ->modalHeading('Confirmar pagamento de parcela'),
            ];
    }

    // public function getColumnSpan(): int | string | array
    // {
    //     return 'full';
    // }
}

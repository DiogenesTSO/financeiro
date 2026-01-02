<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContasResource\Pages;
use App\Models\Conta;
use App\Models\Transacao;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ContasResource extends Resource
{
    protected static ?string $model          = Conta::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('familia_id')
                    ->default(filament()->auth()->user()->familia_id),
                TextInput::make('nome')
                    ->required()
                    ->label('Nome da conta'),
                TextInput::make('saldo_inicial')
                    ->required()
                    ->numeric()
                    ->inputMode('decimal')
                    ->default(0.00)
                    ->prefix('R$')
                    ->label('Saldo inicial da conta'),
                Select::make('tipo')
                    ->label('Tipo da conta')
                    ->options([
                        'corrente'     => 'Conta corrente',
                        'poupanca'     => 'Conta poupança',
                        'cartao'       => 'Cartão de crédito',
                        'dinheiro'     => 'Dinheiro',
                        'investimento' => 'Investimentos',
                    ])
                    ->reactive()
                    ->required(),
                TextInput::make('limite_credito')
                    ->label('Limite de crédito')
                    ->numeric()
                    ->inputMode('decimal')
                    ->prefix('R$')
                    ->visible(fn (Get $get) => $get('tipo') === 'cartao')
                    ->required(fn (Get $get) => $get('tipo') === 'cartao'),
                Toggle::make('status')
                    ->label('Conta ativa')
                    ->inline(false)
                    ->default(true),
                Textarea::make('descricao')
                    ->label('Descrição da conta')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome da conta')
                    ->searchable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->alignCenter()
                    ->color(fn (string $state): string => match ($state) {
                        'corrente'     => 'success',
                        'poupanca'     => 'info',
                        'cartao'       => 'warning',
                        'dinheiro'     => 'gray',
                        'investimento' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'corrente'     => 'Conta corrente',
                        'poupanca'     => 'Conta poupança',
                        'cartao'       => 'Cartão de crédito',
                        'dinheiro'     => 'Dinheiro',
                        'investimento' => 'Investimentos',
                        default     => $state,
                    }),
                TextColumn::make('saldo_inicial')
                    ->label('Saldo inicial')
                    ->alignCenter()
                    ->money('BRL'),
                TextColumn::make('saldo_atual')
                    ->label('Saldo atual')
                    ->alignCenter()
                    ->money('BRL'),
                TextColumn::make('created_at')
                    ->label('Criada Em')
                    ->alignCenter()
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('Atualizada Em')
                    ->dateTime('d/m/Y H:i')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('tipo')
                    ->label('Filtrar por Tipo')
                    ->options([
                        'corrente'     => 'Conta Corrente',
                        'poupanca'     => 'Poupança',
                        'cartao'       => 'Cartão de Crédito',
                        'dinheiro'     => 'Dinheiro em Espécie',
                        'investimento' => 'Investimento',
                ]),
            ])
            ->actions([
                EditAction::make(),
                // DeleteAction::make(),
                Action::make('transferir')
                    ->label('Transferir')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->form([
                        Select::make('conta_origem')
                            ->label('Conta de origem')
                            ->disabled()
                            ->options(fn (Conta $record) => [
                                $record->id => $record->nome,
                            ])
                            ->default(fn (Conta $record) => $record->id)
                            ->dehydrated(),
                        Select::make('conta_destino_id')
                            ->label('Conta destino')
                            ->options(fn (Conta $record) => 
                                Conta::where('familia_id', $record->familia_id)
                                    ->where('id', '!=', $record->id)
                                    ->pluck('nome', 'id')
                            )
                            ->required()
                            ->searchable(),
                        TextInput::make('valor')
                            ->label('Valor')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('R$')
                            ->required(),
                        DatePicker::make('data')
                            ->label('Data')
                            ->default(now())
                            ->required(),
                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->default('Transferência entre contas')
                    ])
                    ->action(function (array $data, Conta $record) {
                        if ($record->saldo_atual < $data['valor']) {
                            throw new \Exception('Saldo insuficiente para essa transferência');
                        }

                        DB::transaction(function () use ($data, $record) {
                            Transacao::create([
                                'familia_id'        => $record->familia_id,
                                'conta_id'          => $record->id,
                                'conta_destino_id'  => $data['conta_destino_id'],
                                'descricao'         => $data['descricao'] . ' (Saída)',
                                'valor'             => $data['valor'],
                                'tipo'              => 'transferir',
                                'data'              => $data['data'],
                                'is_paid'           => true,
                            ]);

                            Transacao::create([
                                'familia_id'        => $record->familia_id,
                                'conta_id'          => $data['conta_destino_id'],
                                'conta_destino_id'  => $record->id,
                                'descricao'         => $data['descricao'] . ' (Entrada)',
                                'valor'             => $data['valor'],
                                'tipo'              => 'transferir',
                                'data'              => $data['data'],
                                'is_paid'           => true,
                            ]);

                            $record->decrement('saldo_atual', $data['valor']);
                            Conta::where('id', $data['conta_destino_id'])
                                ->increment('saldo_atual', $data['valor']);
                        });
                    })
                    ->requiresConfirmation()
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContas::route('/'),
            'create' => Pages\CreateContas::route('/create'),
            'edit'   => Pages\EditContas::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('familia_id', filament()->auth()->user()->familia_id);
    }

    public static function canViewAny(): bool
    {
        return filament()->auth()->user()->familia_id !== null;
    }
}

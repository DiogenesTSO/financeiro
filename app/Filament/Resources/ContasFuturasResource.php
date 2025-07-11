<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContasFuturasResource\Pages;
use App\Filament\Resources\ContasFuturasResource\RelationManagers\ParcelasContasFuturasRelationManager;
use App\Models\Categoria;
use App\Models\Conta;
use App\Models\ContaFutura;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;

class ContasFuturasResource extends Resource
{
    protected static ?string $model = ContaFutura::class;

    protected static ?string $navigationIcon   = 'heroicon-o-calendar-days';
    protected static ?string $modelLabel       = 'Conta futura';
    protected static ?string $pluralModelLabel = 'Contas futuras';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('familia_id')
                    ->default(filament()->auth()->user()->familia_id),
                Section::make('Detalhes das contas')
                    ->columns(2)
                    ->schema([
                        TextInput::make('descricao')
                            ->label('Descrição')
                            ->maxLength(255)
                            ->required(),
                        Select::make('conta_id')
                            ->label('Conta')
                            ->options(
                                Conta::where('familia_id', filament()->auth()->user()->familia_id)
                                    ->pluck('nome', 'id')
                            )
                            ->required()
                            ->searchable(),
                        Select::make('categoria_id')
                            ->label('Categoria')
                            ->options(
                                Categoria::where('familia_id', filament()->auth()->user()->familia_id)
                                    ->pluck('nome', 'id')
                            )
                            ->nullable()
                            ->reactive()
                            ->searchable(),
                        TextInput::make('juros')
                            ->label('Taxa de juros (%)')
                            ->numeric()
                            ->visible(fn (Get $get) => $get('categoria_id') && Categoria::find($get('categoria_id'))?->nome === 'Empréstimos'),
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'receita' => 'Receita',
                                'despesa' => 'Despesa',
                            ])
                            ->required(),
                        Select::make('frequencia')
                            ->label('Frequência')
                            ->options([
                                'dia'       => 'Diário',
                                'semana'    => 'Semanal',
                                'quinzena'  => 'Quinzenal',
                                'mensal'    => 'Mensal',
                                'bimestral' => 'Bimestral',
                                'trimestre' => 'Trimestral',
                                'semestral' => 'Semestral',
                                'anual'     => 'Anual',
                            ])
                            ->required(),
                        DatePicker::make('data_inicio')
                            ->label('Data de Início')
                            ->required()
                            ->default(now()),
                        TextInput::make('valor_total')
                            ->label('Valor Total')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('R$')
                            ->reactive()
                            ->required(),
                        TextInput::make('valor_parcelas')
                            ->label('Valor da Parcela')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('R$')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $valorTotal = floatval($get('valor_total'));
                                $valorParcela = floatval($get('valor_parcelas'));

                                if ($valorTotal > 0 && $valorParcela > 0) {
                                    $qtdParcelas = $valorTotal / $valorParcela;
                                    $set('qtd_parcelas', round($qtdParcelas, 2));
                                }
                            }),
                        TextInput::make('qtd_parcelas')
                            ->label('Número de Parcelas')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'ativo'     => 'Ativo',
                                'concluido' => 'Concluído',
                                'cancelado' => 'Cancelado',
                            ])
                            ->required()
                            ->default('ativo'),
                        Textarea::make('notas')
                            ->label('Notas (Opcional)')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable(),
                TextColumn::make('conta.nome')
                    ->label('Conta')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('categoria.nome')
                    ->label('Categoria')
                    ->alignCenter()
                    ->default('N/A')
                    ->searchable(),
                TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->alignCenter()
                    ->money('BRL'),
                TextColumn::make('valor_parcelas')
                    ->label('Valor Parcela')
                    ->alignCenter()
                    ->money('BRL'),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->alignCenter()
                    ->color(fn (string $state): string => match ($state) {
                        'receita' => 'success',
                        'despesa' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'receita'     => 'Receitas',
                        'despesa'     => 'Despesas',
                        default       => $state,
                    }),
                TextColumn::make('frequencia')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Frequência')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'dia'       => 'Diário',
                        'semana'    => 'Semanal',
                        'quinzena'  => 'Quinzenal',
                        'mensal'    => 'Mensal',
                        'bimestral' => 'Bimestral',
                        'trimestre' => 'Trimestral',
                        'semestral' => 'Semestral',
                        'anual'     => 'Anual',
                        default     => $state,
                    }),
                TextColumn::make('data_inicio')
                    ->alignCenter()
                    ->label('Início')
                    ->date('d/m/Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ativo'     => 'success',
                        'concluido' => 'info',
                        'cancelado' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'ativo'     => 'Ativo',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                        default     => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Criado Em')
                    ->alignCenter()
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ParcelasContasFuturasRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContasFuturas::route('/'),
            'create' => Pages\CreateContasFuturas::route('/create'),
            'edit' => Pages\EditContasFuturas::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()
            ->where('familia_id', filament()->auth()->user()->familia_id);
    }

    public static function canViewAny(): bool
    {
        return filament()->auth()->user()->familia_id !== null;
    }
}

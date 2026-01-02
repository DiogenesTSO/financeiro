<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransacoesResource\Pages;
use App\Models\Categoria;
use App\Models\Conta;
use App\Models\Transacao;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransacoesResource extends Resource
{
    protected static ?string $model = Transacao::class;

    protected static ?string $navigationIcon   = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel  = 'Transações';
    protected static ?string $pluralModelLabel = 'Transações';
    protected static ?string $modelLabel       = 'Transação';

    protected static ?string $recordTitleAttribute = 'descricao';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('familia_id')
                    ->default(filament()->auth()->user()->familia_id),
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
                    ->searchable()
                    ->reactive()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (!$state) {
                                $set('tipo', null);
                                return;
                            }

                            $categoria = Categoria::find($state);

                            $set('tipo', $categoria?->tipo);
                        }),
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255),
                TextInput::make('valor')
                    ->label('Valor')
                    ->numeric()
                    ->inputMode('decimal')
                    ->prefix('R$')
                    ->required(),
                Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'receita'    => 'Receita',
                        'despesa'    => 'Despesa',
                        'transferir' => 'Transferência',
                    ])
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                DatePicker::make('data')
                    ->label('Data')
                    ->required()
                    ->default(now()),
                Textarea::make('notas')
                    ->label('Notas (Opcional)')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Toggle::make('is_paid')
                    ->label('Pago/Recebido')
                    ->helperText('Marque se esta transação já foi efetivada (paga ou recebida).')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data')
                    ->label('Data')
                    ->date('d/m/Y'),
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->alignCenter()
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
                TextColumn::make('valor')
                    ->label('Valor')
                    ->alignCenter()
                    ->money('BRL')
                    ->color(fn (string $state, $record): string => match ($record->tipo) {
                        'receita'    => 'success',
                        'despesa'    => 'danger',
                        'transferir' => 'info',
                        default      => 'gray',
                    }),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'receita'    => 'success',
                        'despesa'    => 'danger',
                        'transferir' => 'info',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'receita'     => 'Receitas',
                        'despesa'     => 'Despesas',
                        'transferir'  => 'Transferencias',
                        default       => $state,
                    }),
                IconColumn::make('is_paid')
                    ->label('Pago/Recebido')
                    ->alignCenter()
                    ->tooltip(fn (bool $state): string => $state ? 'Já foi pago/recebido' : 'Ainda não foi pago ou recebido')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Criada Em')
                    ->alignCenter()
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Filtrar por Tipo')
                    ->options([
                        'receita'    => 'Receita',
                        'despesa'    => 'Despesa',
                        'transferir' => 'Transferência',
                    ]),
                SelectFilter::make('conta_id')
                    ->label('Filtrar por Conta')
                    ->options(
                        Conta::where('familia_id', filament()->auth()->user()->familia_id)
                            ->pluck('nome', 'id')
                    )
                    ->searchable(),
                SelectFilter::make('categoria_id')
                    ->label('Filtrar por Categoria')
                    ->options(
                        Categoria::where('familia_id', filament()->auth()->user()->familia_id)
                            ->pluck('nome', 'id')
                    )
                    ->searchable(),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from_date')
                            ->label('De')
                            ->placeholder(fn ($state): string => 'Jan 1, ' . now()->year),
                        DatePicker::make('to_date')
                            ->label('Até')
                            ->placeholder(fn ($state): string => 'Dec 31, ' . now()->year),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['from_date'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('data', '>=', $date),
                                )
                                ->when(
                                    $data['to_date'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('data', '<=', $date),
                                );
                        })
                    ->indicateUsing(function (array $data): array {
                            $indicators = [];
                            if ($data['from_date'] ?? null) {
                                $indicators['from_date'] = 'De ' . Carbon::parse($data['from_date'])->toFormattedDateString();
                            }
                            if ($data['to_date'] ?? null) {
                                $indicators['to_date'] = 'Até ' . Carbon::parse($data['to_date'])->toFormattedDateString();
                            }
                            return $indicators;
                        }),
                TernaryFilter::make('is_paid')
                    ->label('Status de Pagamento')
                    ->trueLabel('Pago/Recebido')
                    ->falseLabel('Pendente'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransacoes::route('/'),
            'create' => Pages\CreateTransacoes::route('/create'),
            'edit'   => Pages\EditTransacoes::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('familia_id', filament()->auth()->user()->familia_id)
            ->orderByDesc('created_at');
    }

    public static function canViewAny(): bool
    {
        return filament()->auth()->user()->familia_id !== null;
    }
}

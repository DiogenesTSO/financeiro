<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContasResource\Pages;
use App\Filament\Resources\ContasResource\RelationManagers;
use App\Models\Conta;
use App\Models\Contas;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContasResource extends Resource
{
    protected static ?string $model = Conta::class;

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
                    // ->mask(fn (Mask $mask) => $mask
                    //     ->numeric()
                    //     ->decimalPlaces(2)
                    //     ->thousandsSeparator('.')
                    //     ->decimalSeparator(',')
                    // )
                    ->label('Saldo da conta'),
                Select::make('tipo')
                    ->label('Tipo da conta')
                    ->options([
                        'corrente'     => 'Conta corrente',
                        'poupanca'     => 'Conta poupança',
                        'cartao'       => 'Cartão de crédito',
                        'dinheiro'     => 'Dinheiro',
                        'investimento' => 'Investimentos',
                    ])
                    ->required(),
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
                TextColumn::make('current_balance')
                    ->label('Saldo Atual')
                    ->alignCenter()
                    ->money('BRL')
                    ->color(fn (float $state): string => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('created_at')
                    ->label('Criada Em')
                    ->alignCenter()
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                DeleteAction::make(),
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
}

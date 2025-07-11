<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FamiliaResource\Pages;
use App\Models\Familia;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class FamiliaResource extends Resource
{
    protected static ?string $model           = Familia::class;

    protected static ?string $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Configurações';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nome')
                    ->label('Nome da familia')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome'),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->alignCenter()
                    ->dateTime('d/m/Y H:i')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFamilias::route('/'),
            'create' => Pages\CreateFamilia::route('/create'),
            'edit'   => Pages\EditFamilia::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()
            ->where('id', filament()->auth()->user()->familia_id);
    }
}

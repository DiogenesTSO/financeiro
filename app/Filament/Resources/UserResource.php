<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Familia;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon   = 'heroicon-o-users';
    protected static ?string $navigationGroup  = 'Configurações';
    protected static ?string $modelLabel       = 'Usuário';
    protected static ?string $pluralModelLabel = 'Usuários';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('familia_id')
                    ->label('Familia')
                    ->searchable()
                    ->relationship('familia', 'nome')
                    ->options(Familia::orderBy('nome')->limit(20)->pluck('nome', 'id'))
                    ->required(),
                    // ->disabled(fn (string $operation) => $operation === 'edit'),
                TextInput::make('name')
                    ->required()
                    ->label('Nome'),
                TextInput::make('usuario')
                    ->label('Usuario'),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->label('Email'),
                TextInput::make('password')
                    ->password()
                    ->minLength(8)
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->label('Senha'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('familia.nome')
                    ->searchable()
                    ->label('Familia'),
                TextColumn::make('name')
                    ->label('Nome'),
                TextColumn::make('email')
                    ->alignCenter()
                    ->label('Email'),
                TextColumn::make('created_at')
                    ->label('Data da criação')
                    ->dateTime('d/m/Y H:i')
                    ->alignCenter()
            ])
            ->filters([
                //
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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

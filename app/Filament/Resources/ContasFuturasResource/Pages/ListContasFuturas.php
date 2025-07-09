<?php

namespace App\Filament\Resources\ContasFuturasResource\Pages;

use App\Filament\Resources\ContasFuturasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContasFuturas extends ListRecords
{
    protected static string $resource = ContasFuturasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

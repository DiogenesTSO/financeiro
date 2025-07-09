<?php

namespace App\Filament\Resources\ContasResource\Pages;

use App\Filament\Resources\ContasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContas extends ListRecords
{
    protected static string $resource = ContasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

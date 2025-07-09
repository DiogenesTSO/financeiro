<?php

namespace App\Filament\Resources\TransacoesResource\Pages;

use App\Filament\Resources\TransacoesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransacoes extends EditRecord
{
    protected static string $resource = TransacoesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

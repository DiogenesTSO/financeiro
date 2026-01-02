<?php

namespace App\Filament\Resources\TransacoesResource\Pages;

use App\Filament\Resources\TransacoesResource;
use App\Models\Conta;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransacoes extends CreateRecord
{
    protected static string $resource = TransacoesResource::class;

    protected function afterCreate(): void
    {
        $transacao = $this->record;

        if (!$transacao->is_paid) {
            return;
        }

        $conta = Conta::find($transacao->conta_id);

        if (!$conta) {
            return;
        }

        match ($transacao->tipo) {
            'receita' => $conta->increment('saldo_atual', $transacao->valor),
            'despesa' => $conta->decrement('saldo_atual', $transacao->valor),
            default   => null,
        };
    }
}

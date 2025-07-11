<?php

namespace App\Filament\Widgets;

use App\Models\Categoria;
use App\Models\Conta;
use App\Models\Transacao;
use Filament\Widgets\Widget;

class BoasVindas extends Widget
{
    protected static string $view = 'filament.widgets.boas-vindas';

    public $etapa = 'familia';

    public function mount(): void
    {
        $user = filament()->auth()->user();

        if (!$user->familia_id) {
            $this->etapa = 'familia';
            return;
        }

        $temContas = Conta::where('familia_id', $user->familia_id)->exists();
        if (!$temContas) {
            $this->etapa = 'conta';
            return;
        }

        $temCategorias = Categoria::where('familia_id', $user->familia_id)->exists();
        if (!$temCategorias) {
            $this->etapa = 'categorias';
            return;
        }

        $temtransacao = Transacao::where('familia_id', $user->familia_id)->exists();
        if (!$temtransacao) {
            $this->etapa = 'transacao';
            return;
        }

    }

    public static function canView(): bool
    {
        return true;
    }

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
}

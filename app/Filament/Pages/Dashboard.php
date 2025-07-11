<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BoasVindas;
use App\Filament\Widgets\FinanceiroStats;
use App\Filament\Widgets\GraficoDespesas;
use App\Filament\Widgets\GraficoParcelas;
use App\Models\Transacao;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $title = 'Dashboard Financeira';

    public function getWidgets(): array
    {
        $temDados = Transacao::where('familia_id', filament()->auth()->user()->familia_id)->exists();

        if(!$temDados) {
            return [
                BoasVindas::class,
            ];
        }

        return [
            FinanceiroStats::class,
            GraficoDespesas::class,
            GraficoParcelas::class,
        ];
    }
}

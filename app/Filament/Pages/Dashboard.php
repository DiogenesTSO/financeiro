<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinanceiroStats;
use App\Filament\Widgets\GraficoDespesas;
use App\Filament\Widgets\GraficoParcelas;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $title = 'Dashboard Financeira';

    public function getWidgets(): array
    {
        return [
            FinanceiroStats::class,
            GraficoDespesas::class,
            GraficoParcelas::class,
        ];
    }
}

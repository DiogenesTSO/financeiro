<?php

namespace App\Filament\Widgets;

use App\Models\Transacao;
use Filament\Widgets\ChartWidget;

class GraficoParcelas extends ChartWidget
{
    protected static ?string $heading = 'Evolução Mensal: Receitas vs Despesas';

    protected function getData(): array
    {
        $meses    = collect();
        $receitas = collect();
        $despesas = collect();

        for ($i = 5; $i >= 0; $i--) {
            $inicio = now()->copy()->subMonths($i)->startOfMonth();
            $fim    = now()->copy()->subMonths($i)->endOfMonth();
            $label  = $inicio->format('M/Y');

            $meses->push($label);

            $receitas->push(
                Transacao::where('tipo', 'receita')
                    ->where('familia_id', filament()->auth()->user()->familia_id)
                    ->whereBetween('data', [$inicio, $fim])
                    ->sum('valor')
            );

            $despesas->push(
                Transacao::where('is_paid', true)
                    ->where('tipo', 'despesa')
                    ->where('familia_id', filament()->auth()->user()->familia_id)
                    ->whereBetween('data', [$inicio, $fim])
                    ->sum('valor')
            );
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Receitas',
                    'data'            => $receitas,
                    'backgroundColor' => '#34d399',
                    'borderColor'     => '#10b981',
                    'fill'            => false,
                ],
                [
                    'label'           => 'Despesas',
                    'data'            => $despesas,
                    'backgroundColor' => '#f87171',
                    'borderColor'     => '#ef4444',
                    'fill'            => false,
                ],
            ],
            'labels' => $meses,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

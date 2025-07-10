<?php

namespace App\Filament\Widgets;

use App\Models\Transacao;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class GraficoDespesas extends ChartWidget
{
    protected static ?string $heading = 'Despesas por Categoria (mês atual)';
    protected static ?string $maxHeight = '275px';

    protected function getData(): array
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes    = Carbon::now()->endOfMonth();

        $dados = Transacao::selectRaw('categorias.nome as categoria, SUM(valor) as total')
            ->join('categorias', 'transacoes.categoria_id', '=', 'categorias.id')
            ->where('transacoes.tipo', 'despesa')
            ->where('transacoes.is_paid', true)
            ->whereBetween('transacoes.data', [$inicioMes, $fimMes])
            ->groupBy('categorias.nome')
            ->pluck('total', 'categoria');

        return [
            'datasets' => [
                [
                    'label' => 'Despesas',
                    'data'  => $dados->values(),
                    'backgroundColor' => [
                        '#f87171', '#fb923c', '#facc15', '#4ade80', '#60a5fa',
                        '#a78bfa', '#f472b6', '#94a3b8', '#34d399', '#e879f9'
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $dados->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}

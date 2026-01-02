<?php

namespace App\Filament\Widgets;

use App\Models\Conta;
use App\Models\ParcelaContaFutura;
use App\Models\Transacao;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceiroStats extends BaseWidget
{
    protected function getStats(): array
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes    = Carbon::now()->endOfMonth();

        $familiaId = filament()->auth()->user()->familia_id;
        $contas = Conta::where('familia_id', $familiaId)->get();

        $despesasMes = Transacao::where('tipo', 'despesa')
            ->where('is_paid', true)
            ->where('familia_id', filament()->auth()->user()->familia_id)
            ->whereBetween('data', [$inicioMes, $fimMes])
            ->sum('valor');

        $parcelasAVencer = ParcelaContaFutura::where('is_pad', false)
            ->whereBetween('vencimento', [$inicioMes, $fimMes])
            ->whereHas('contaFutura', function ($query) {
                $query->where('familia_id', filament()->auth()->user()->familia_id);
            })
            ->sum('valor');

        $parcelasPagas = ParcelaContaFutura::whereHas('contaFutura', function ($query) {
                $query->where('familia_id', filament()->auth()->user()->familia_id)
                    ->where('status', 'ativo');
            })
            ->where('is_pad', true)
            ->count();

        $totalParcelas = ParcelaContaFutura::whereHas('contaFutura', function ($query) {
                $query->where('familia_id', filament()->auth()->user()->familia_id)
                    ->where('status', 'ativo');
            })
            ->count();

        $statsContas = $contas->map(function ($conta) {
            return Stat::make(
                "💰 {$conta->nome}",
                    'R$ ' . number_format($conta->saldo_atual, 2, ',', '.')
            )
            ->description('Saldo atual da conta');
        });
        $statsGerais = [
            Stat::make('📉 Despesas do Mês', 'R$ ' . number_format($despesasMes, 2, ',', '.'))
                ->description('Transações pagas no mês'),
            Stat::make('📅 Parcelas a vencer entre', 'R$ ' . number_format($parcelasAVencer, 2, ',', '.'))
                ->description(now()->startOfMonth()->format('d/m/Y') . ' - ' . now()->endOfMonth()->format('d/m/Y') . " | Pagas {$parcelasPagas} de {$totalParcelas}")
        ];

        return $statsContas
            ->merge($statsGerais)
            ->values()
            ->all();
    }
}

<?php

namespace App\Filament\Widgets;

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

        $saldoAtual = Transacao::where('is_paid', true)
            ->where('familia_id', filament()->auth()->user()->familia_id)
            ->selectRaw("SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END) as saldo")
            ->value('saldo');

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

        return [
            Stat::make('💰 Saldo Atual', 'R$ ' . number_format($saldoAtual, 2, ',', '.'))
                ->description('Receitas - Despesas'),
            Stat::make('📉 Despesas do Mês', 'R$ ' . number_format($despesasMes, 2, ',', '.'))
                ->description('Transações pagas no mês'),
            Stat::make('📅 Parcelas a vencer entre', 'R$ ' . number_format($parcelasAVencer, 2, ',', '.'))
                ->description(now()->startOfMonth()->format('d/m/Y') . ' - ' . now()->endOfMonth()->format('d/m/Y') . " | Pagas {$parcelasPagas} de {$totalParcelas}")
        ];
    }
}

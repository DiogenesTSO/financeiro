<?php

namespace App\Services;

use App\Models\ParcelaContaFutura;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ParcelasServices
{
    public function recalcularParcelasComValores($conta): void
    {
        // Pega parcelas pagas e não pagas
        $parcelas = $conta->parcelas()
            ->orderBy('qtd_parcelas')
            ->get()
            ->partition(fn ($parcela) => $parcela->is_pad);

        [$parcelasPagas, $parcelasNaoPagas] = $parcelas;

        $valorPago    = $parcelasPagas->sum('valor_pago');
        $saldoDevedor = $conta->valor_total - $valorPago;

        if ($saldoDevedor <= 0) {
            return;
        }

        $valorParcela = $conta->valor_parcelas;
        $juros = $conta->juros ?? 0;
        $jurosMensal = $juros / 100;
        $vencimentoInicial = now()->parse($conta->data_inicio)->addMonths($parcelasPagas->count());

        if ($parcelasNaoPagas->isNotEmpty()) {
            $conta->parcelas()
                ->where('is_pad', false)
                ->delete();
        }

        $novasParcelas = $this->calcularNovasParcelas(
            $conta->id,
            $saldoDevedor,
            $valorParcela,
            $jurosMensal,
            $vencimentoInicial,
            $parcelasPagas->count()
        );

        if (!empty($novasParcelas)) {
            ParcelaContaFutura::insert($novasParcelas);
            $conta->update([
                'qtd_parcelas' => $parcelasPagas->count() + count($novasParcelas),
            ]);
        }
    }

    public function calcularNovasParcelas(int $contaId, float $saldoDevedor, float $valorParcela, float $jurosMensal, Carbon $vencimentoInicial, int $parcelasPagasCount): array
    {
        $parcelas      = [];
        $numeroParcela = $parcelasPagasCount + 1;
        $mesOffset     = 0;

        while ($saldoDevedor > 0.01) {
            $jurosMes      = $saldoDevedor * $jurosMensal;
            $parcelaBase   = min($valorParcela, $saldoDevedor);
            $valorComJuros = $parcelaBase + $jurosMes;

            $parcelas[] = [
                'conta_futura_id' => $contaId,
                'qtd_parcelas'    => $numeroParcela,
                'valor'           => round($valorComJuros, 2),
                'vencimento'      => $vencimentoInicial->copy()->addMonths($mesOffset),
                'is_pad'          => false,
            ];

            $saldoDevedor -= $parcelaBase;
            $numeroParcela++;
            $mesOffset++;

            if ($mesOffset > 1000) {
                throw new \Exception('Erro no cálculo de parcelas: loop infinito detectado');
            }
        }

        return $parcelas;
    }

    public function atualizarDatasParcelas($conta): void
    {
        $dataInicio = now()->parse($conta->data_inicio);

        $parcelas = $conta->parcelas()
            ->orderBy('qtd_parcelas')
            ->get(['id', 'qtd_parcelas']);

        $updates = [];

        foreach ($parcelas as $parcela) {
            $novaData  = $dataInicio->copy()->addMonths($parcela->qtd_parcelas - 1);
            $updates[] = [
                'id'         => $parcela->id,
                'vencimento' => $novaData,
            ];
        }

        if (!empty($updates)) {
            $this->bulkUpdateVencimentos($updates);
        }
    }

    public function bulkUpdateVencimentos(array $updates): void
    {
        // Implementação de atualização em lote
        foreach (array_chunk($updates, 100) as $chunk) {
            $cases = [];
            $ids   = [];

            foreach ($chunk as $update) {
                $cases[] = "WHEN id = {$update['id']} THEN '{$update['vencimento']}'";
                $ids[]   = $update['id'];
            }

            $casesString = implode(' ', $cases);
            $idsString   = implode(',', $ids);

            DB::statement("
                UPDATE parcelas_contas_futuras
                SET vencimento = CASE {$casesString} END,
                    updated_at = NOW()
                WHERE id IN ({$idsString})
            ");
        }
    }
}

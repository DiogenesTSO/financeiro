<?php

namespace App\Filament\Resources\ContasFuturasResource\Pages;

use App\Filament\Resources\ContasFuturasResource;
use App\Models\ParcelaContaFutura;
use Filament\Resources\Pages\CreateRecord;

class CreateContasFuturas extends CreateRecord
{
    protected static string $resource = ContasFuturasResource::class;

    protected function afterCreate(): void
    {
        $total             = $this->record->valor_total;
        $valorParcela      = $this->record->valor_parcelas;
        $juros             = $this->record->juros ?? 0;
        $vencimentoInicial = now()->parse($this->record->data_inicio);

        $parcelasCriadas = [];
        $saldoDevedor = $total;
        $i = 0;

        while ($saldoDevedor > 0.01) {
            $jurosMes = $saldoDevedor * ($juros / 100);
            $parcelaBase = min($valorParcela, $saldoDevedor);
            $valorComJuros = $parcelaBase + $jurosMes;

            $parcelasCriadas[] = [
                'qtd_parcelas'  => $i + 1,
                'valor'         => round($valorComJuros, 2),
                'vencimento'    => $vencimentoInicial->copy()->addMonths($i),
            ];

            $saldoDevedor -= $parcelaBase;
            $i++;
        }

        foreach ($parcelasCriadas as $parcela) {
            ParcelaContaFutura::create([
                'conta_futura_id'   => $this->record->id,
                'qtd_parcelas'      => $parcela['qtd_parcelas'],
                'valor'             => $parcela['valor'],
                'vencimento'        => $parcela['vencimento'],
            ]);
        }

        $this->record->update([
            'qtd_parcelas' => count($parcelasCriadas),
        ]);
    }
}

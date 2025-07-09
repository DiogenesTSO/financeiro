<?php

namespace App\Filament\Resources\ContasFuturasResource\Pages;

use App\Filament\Resources\ContasFuturasResource;
use App\Models\ParcelaContaFutura;
use App\Services\ParcelasService;
use App\Services\ParcelasServices;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditContasFuturas extends EditRecord
{
    protected static string $resource = ContasFuturasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected ParcelasServices $parcelasService;
    public function boot(): void
    {
        $this->parcelasService = app(ParcelasServices::class);
    }

    protected function afterSave(): void
    {
        $camposAlterados = $this->record->getChanges();

        $valorCampos  = ['valor_total', 'valor_parcelas', 'qtd_parcelas', 'juros'];
        $temAlteracao = !empty(array_intersect_key($camposAlterados, array_flip($valorCampos)));

        if ($temAlteracao) {
            $this->parcelasService->recalcularParcelasComValores($this->record);
        } elseif (array_key_exists('data_inicio', $camposAlterados)) {
            $this->parcelasService->atualizarDatasParcelas($this->record);
        }
    }
}

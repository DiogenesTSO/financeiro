<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParcelaContaFutura extends Model
{
    use HasFactory;

    protected $table = 'parcelas_contas_futuras';
    protected $fillable = [
        'conta_futura_id',
        'qtd_parcelas',
        'valor',
        'vencimento',
        'is_pad',
        'valor_pago',
        'pago_em',
    ];

    protected $casts = [
        'vencimento' => 'date',
        'pago_em'    => 'date',
        'is_paid'    => 'boolean',
    ];

    //Relacionamentos
    public function contaFutura(): BelongsTo
    {
        return $this->belongsTo(ContaFutura::class, 'conta_futura_id');
    }
}

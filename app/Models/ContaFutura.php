<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContaFutura extends Model
{
    use HasFactory;

    protected $table = 'contas_futuras';
    protected $fillable = [
        'familia_id',
        'conta_id',
        'categoria_id',
        'descricao',
        'valor_total',
        'juros',
        'valor_parcelas',
        'qtd_parcelas',
        'parcelas_pagas',
        'tipo',
        'frequencia',
        'data_inicio',
        'data_fim',
        'status',
        'notas',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function familia(): BelongsTo
    {
        return $this->belongsTo(familia::class);
    }

    public function conta(): BelongsTo
    {
        return $this->belongsTo(Conta::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function parcelas(): HasMany
    {
        return $this->hasMany(ParcelaContaFutura::class, 'conta_futura_id');
    }
}

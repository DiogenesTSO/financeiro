<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transacao extends Model
{
    use HasFactory;

    protected $table = 'transacoes';
    protected $fillable = [
        'familia_id',
        'conta_id',
        'categoria_id',
        'conta_destino_id',
        'descricao',
        'valor',
        'tipo',
        'data',
        'notas',
        'is_paid',
    ];

    protected $casts = [
        'data'    => 'date',
        'is_paid' => 'boolean',
        'valor'   => 'decimal:2',
    ];

    //Relacionamentos
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
}

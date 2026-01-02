<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conta extends Model
{
    use HasFactory;

    protected $fillable = [
        'familia_id',
        'nome',
        'saldo_inicial',
        'tipo',
        'limite_credito',
        'descricao',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($conta) {
            $conta->saldo_atual = $conta->saldo_inicial;
        });
    }

    //Relacionamento
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function familia(): BelongsTo
    {
        return $this->belongsTo(familia::class);
    }

    public function transacoes(): HasMany
    {
        return $this->hasMany(Transacao::class);
    }

    /**
     * Calcula o saldo atual da conta.
     * Pode ser um acessor ou um método, dependendo de como você quer usá-lo.
     * Para Filament, um acessor é útil para exibir na tabela/formulário.
     */
    // public function getCurrentBalanceAttribute(): float
    // {
    //     $renda = $this->transacoes()->whereIn('tipo', ['renda', 'transferir'])
    //                                 ->sum('valor');

    //     $despesas = $this->transacoes()->where('tipo', 'despesas')
    //                                 ->sum('valor');

    //     // Se você tiver transferências entre contas, a lógica pode ser mais complexa.
    //     // Por simplicidade, aqui consideramos 'transfer' como entrada na conta de destino.
    //     // Se for uma transferência de saída, ela seria uma 'expense' da conta de origem.
    //     // A forma mais robusta é ter um campo 'from_account_id' e 'to_account_id' na transação.
    //     // Para este setup, assumimos que 'transfer' é sempre um crédito para esta conta.

    //     return $this->valor_inicial + $renda - $despesas;
    // }
}

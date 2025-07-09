<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Familia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
    ];

    //Relacionamentos
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contas(): HasMany
    {
        return $this->hasMany(Conta::class);
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }

    public function transacoes(): HasMany
    {
        return $this->hasMany(Transacao::class);
    }

    public function contasFuturas(): HasMany
    {
        return $this->hasMany(ContaFutura::class);
    }
}

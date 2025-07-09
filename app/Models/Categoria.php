<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'familia_id',
        'nome',
        'tipo',
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

    public function transacoes(): HasMany
    {
        return $this->hasMany(Transacao::class);
    }
}

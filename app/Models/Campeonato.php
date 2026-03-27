<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campeonato extends Model
{
    protected $table = 'campeonatos';

    protected $fillable = [
        'nome',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function times(): BelongsToMany
    {
        return $this->belongsToMany(Time::class, 'campeonato_time', 'campeonato_id', 'time_id')
            ->withPivot(['ordem_inscricao', 'pontuacao_total', 'gols_fora_de_casa'])
            ->orderBy('campeonato_time.ordem_inscricao')
            ->withTimestamps();
    }

    public function partidas(): HasMany
    {
        return $this->hasMany(Partida::class, 'campeonato_id');
    }

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isEmAndamento(): bool
    {
        return $this->status === 'em_andamento';
    }

    public function isFinalizado(): bool
    {
        return $this->status === 'finalizado';
    }
}

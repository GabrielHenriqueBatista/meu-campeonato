<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read CampeonatoTimePivot $pivot
 */
class Time extends Model
{
    protected $table = 'times';

    protected $fillable = [
        'nome',
    ];

    public function campeonatos(): BelongsToMany
    {
        return $this->belongsToMany(Campeonato::class, 'campeonato_time', 'time_id', 'campeonato_id')
            ->using(CampeonatoTimePivot::class)
            ->withPivot(['ordem_inscricao', 'pontuacao_total', 'gols_fora_de_casa'])
            ->withTimestamps();
    }

    public function partidasMandante(): HasMany
    {
        return $this->hasMany(Partida::class, 'time_mandante_id');
    }

    public function partidasVisitante(): HasMany
    {
        return $this->hasMany(Partida::class, 'time_visitante_id');
    }

    public function partidasVencidas(): HasMany
    {
        return $this->hasMany(Partida::class, 'time_vencedor_id');
    }
}

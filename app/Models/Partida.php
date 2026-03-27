<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partida extends Model
{
    protected $table = 'partidas';

    protected $fillable = [
        'campeonato_id',
        'fase',
        'time_mandante_id',
        'time_visitante_id',
        'gols_mandante',
        'gols_visitante',
        'time_vencedor_id',
    ];

    protected $casts = [
        'gols_mandante'  => 'integer',
        'gols_visitante' => 'integer',
    ];

    public function campeonato(): BelongsTo
    {
        return $this->belongsTo(Campeonato::class, 'campeonato_id');
    }

    public function timeMandante(): BelongsTo
    {
        return $this->belongsTo(Time::class, 'time_mandante_id');
    }

    public function timeVisitante(): BelongsTo
    {
        return $this->belongsTo(Time::class, 'time_visitante_id');
    }

    public function timeVencedor(): BelongsTo
    {
        return $this->belongsTo(Time::class, 'time_vencedor_id');
    }

    public function foiSimulada(): bool
    {
        return $this->gols_mandante !== null && $this->gols_visitante !== null;
    }
}

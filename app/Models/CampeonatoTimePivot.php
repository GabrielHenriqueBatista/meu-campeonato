<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CampeonatoTimePivot extends Pivot
{
    protected $table = 'campeonato_time';

    protected $fillable = [
        'campeonato_id',
        'time_id',
        'ordem_inscricao',
        'pontuacao_total',
        'gols_fora_de_casa',
    ];

    protected $casts = [
        'ordem_inscricao'   => 'integer',
        'pontuacao_total'   => 'integer',
        'gols_fora_de_casa' => 'integer',
    ];
}

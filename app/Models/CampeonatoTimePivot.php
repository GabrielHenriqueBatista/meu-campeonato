<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CampeonatoTimePivot extends Pivot
{
    protected $table = 'campeonato_time';

    public int $ordem_inscricao;
    public int $pontuacao_total;
    public int $gols_fora_de_casa;
}

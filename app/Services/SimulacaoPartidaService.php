<?php

namespace App\Services;

use App\Models\Campeonato;
use App\Models\CampeonatoTimePivot;
use App\Models\Partida;
use App\Models\Time;
use Illuminate\Support\Facades\DB;

readonly class SimulacaoPartidaService
{
    public function __construct(
        private PlacarPythonService $placarPythonService,
    ) {
    }

    public function simular(Partida $partida): void
    {
        $placar = $this->placarPythonService->obterPlacar();

        $golsMandante  = $placar['gols_mandante'];
        $golsVisitante = $placar['gols_visitante'];

        $vencedor = $this->determinarVencedor(
            $partida,
            $golsMandante,
            $golsVisitante,
        );

        $partida->update([
            'gols_mandante'    => $golsMandante,
            'gols_visitante'   => $golsVisitante,
            'time_vencedor_id' => $vencedor->id,
        ]);

        $this->atualizarPontuacao($partida, $golsMandante, $golsVisitante);
    }

    private function determinarVencedor(
        Partida $partida,
        int $golsMandante,
        int $golsVisitante,
    ): Time {
        if ($golsMandante !== $golsVisitante) {
            /** @var Time */
            return $golsMandante > $golsVisitante
                ? Time::findOrFail($partida->time_mandante_id)
                : Time::findOrFail($partida->time_visitante_id);
        }

        return $this->aplicarDesempate($partida);
    }

    private function aplicarDesempate(Partida $partida): Time
    {
        /** @var Campeonato */
        $campeonato = Campeonato::findOrFail($partida->campeonato_id);

        /** @var Time */
        $mandante = Time::findOrFail($partida->time_mandante_id);

        /** @var Time */
        $visitante = Time::findOrFail($partida->time_visitante_id);

        $pivotMandante  = $this->obterPivot($campeonato, $mandante);
        $pivotVisitante = $this->obterPivot($campeonato, $visitante);

        // Regra 2: maior pontuacao_total
        if ($pivotMandante->pontuacao_total !== $pivotVisitante->pontuacao_total) {
            return $pivotMandante->pontuacao_total > $pivotVisitante->pontuacao_total
                ? $mandante
                : $visitante;
        }

        // Regra 3: maior gols_fora_de_casa
        if ($pivotMandante->gols_fora_de_casa !== $pivotVisitante->gols_fora_de_casa) {
            return $pivotMandante->gols_fora_de_casa > $pivotVisitante->gols_fora_de_casa
                ? $mandante
                : $visitante;
        }

        // Regra 4: menor ordem_inscricao (inscrito primeiro)
        return $pivotMandante->ordem_inscricao < $pivotVisitante->ordem_inscricao
            ? $mandante
            : $visitante;
    }

    private function obterPivot(Campeonato $campeonato, Time $time): CampeonatoTimePivot
    {
        /** @var CampeonatoTimePivot */
        return $campeonato->times()
            ->wherePivot('time_id', $time->id)
            ->firstOrFail()
            ->pivot;
    }

    private function atualizarPontuacao(
        Partida $partida,
        int $golsMandante,
        int $golsVisitante,
    ): void {
        // Mandante: +gols marcados -gols sofridos
        \Illuminate\Support\Facades\DB::table('campeonato_time')
            ->where('campeonato_id', $partida->campeonato_id)
            ->where('time_id', $partida->time_mandante_id)
            ->update([
                'pontuacao_total' => DB::raw("pontuacao_total + {$golsMandante} - {$golsVisitante}"),
            ]);

        // Visitante: +gols marcados -gols sofridos + gols fora de casa
        \Illuminate\Support\Facades\DB::table('campeonato_time')
            ->where('campeonato_id', $partida->campeonato_id)
            ->where('time_id', $partida->time_visitante_id)
            ->update([
                'pontuacao_total'   => DB::raw("pontuacao_total + {$golsVisitante} - {$golsMandante}"),
                'gols_fora_de_casa' => DB::raw("gols_fora_de_casa + {$golsVisitante}"),
            ]);
    }
}

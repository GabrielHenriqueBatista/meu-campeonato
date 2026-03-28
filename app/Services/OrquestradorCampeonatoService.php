<?php

namespace App\Services;

use App\Models\Campeonato;
use App\Models\Partida;
use Illuminate\Support\Facades\DB;

class OrquestradorCampeonatoService
{
    public function __construct(
        private readonly ChaveamentoCampeonatoService $chaveamento,
        private readonly SimulacaoPartidaService $simulacao,
    ) {
    }

    public function executar(Campeonato $campeonato): void
    {
        DB::transaction(function () use ($campeonato): void {
            $campeonato->update(['status' => 'em_andamento']);

            // Quartas de final
            $this->chaveamento->gerarQuartasDeFinal($campeonato);
            $this->simularFase($campeonato, 'quartas_de_final');

            // Semifinais
            $this->chaveamento->gerarSemifinais($campeonato);
            $this->simularFase($campeonato, 'semifinal');

            // Terceiro lugar
            $this->chaveamento->gerarTerceiroLugar($campeonato);
            $this->simularFase($campeonato, 'terceiro_lugar');

            // Final
            $this->chaveamento->gerarFinal($campeonato);
            $this->simularFase($campeonato, 'final');

            $campeonato->update(['status' => 'finalizado']);
        });
    }

    private function simularFase(Campeonato $campeonato, string $fase): void
    {
        $partidas = $campeonato->partidas()
            ->where('fase', $fase)
            ->get();

        foreach ($partidas as $partida) {
            /** @var Partida $partida */
            $this->simulacao->simular($partida);
        }
    }
}

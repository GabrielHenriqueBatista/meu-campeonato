<?php

namespace App\Services;

use App\Models\Campeonato;
use App\Models\Partida;
use App\Models\Time;
use Illuminate\Support\Collection;

class ChaveamentoCampeonatoService
{
    public function gerarQuartasDeFinal(Campeonato $campeonato): void
    {
        /** @var Collection<int, Time> $times */
        $times = Time::query()
            ->whereHas('campeonatos', fn ($q) => $q->where('campeonatos.id', $campeonato->id))
            ->get()
            ->shuffle();

        $this->criarPartidas($campeonato, $times, 'quartas_de_final');
    }

    public function gerarSemifinais(Campeonato $campeonato): void
    {
        /** @var Collection<int, Time> $vencedores */
        $vencedores = $this->obterVencedoresDaFase($campeonato, 'quartas_de_final');

        $this->criarPartidas($campeonato, $vencedores, 'semifinal');
    }

    public function gerarTerceiroLugar(Campeonato $campeonato): void
    {
        /** @var Collection<int, Time> $perdedores */
        $perdedores = $this->obterPerdedoresDaFase($campeonato, 'semifinal');

        $this->criarPartidas($campeonato, $perdedores, 'terceiro_lugar');
    }

    public function gerarFinal(Campeonato $campeonato): void
    {
        /** @var Collection<int, Time> $vencedores */
        $vencedores = $this->obterVencedoresDaFase($campeonato, 'semifinal');

        $this->criarPartidas($campeonato, $vencedores, 'final');
    }

    /**
     * @return Collection<int, Time>
     */
    private function obterVencedoresDaFase(Campeonato $campeonato, string $fase): Collection
    {
        /** @var Collection<int, Time> */
        return Time::query()
            ->whereIn('id', function ($query) use ($campeonato, $fase) {
                $query->select('time_vencedor_id')
                    ->from('partidas')
                    ->where('campeonato_id', $campeonato->id)
                    ->where('fase', $fase)
                    ->whereNotNull('time_vencedor_id');
            })
            ->get();
    }

    /**
     * @return Collection<int, Time>
     */
    private function obterPerdedoresDaFase(Campeonato $campeonato, string $fase): Collection
    {
        $partidas = Partida::query()
            ->where('campeonato_id', $campeonato->id)
            ->where('fase', $fase)
            ->get();

        $idsPerdedores = $partidas->map(function (Partida $partida): int {
            return $partida->time_vencedor_id === $partida->time_mandante_id
                ? (int) $partida->time_visitante_id
                : (int) $partida->time_mandante_id;
        });

        /** @var Collection<int, Time> */
        return Time::query()->whereIn('id', $idsPerdedores)->get();
    }

    /**
     * @param Collection<int, Time> $times
     */
    private function criarPartidas(Campeonato $campeonato, Collection $times, string $fase): void
    {
        $times = $times->shuffle();

        for ($i = 0; $i < $times->count(); $i += 2) {
            Partida::create([
                'campeonato_id'     => $campeonato->id,
                'fase'              => $fase,
                'time_mandante_id'  => $times[$i]->id,
                'time_visitante_id' => $times[$i + 1]->id,
            ]);
        }
    }
}

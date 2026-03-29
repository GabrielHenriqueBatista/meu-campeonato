<?php

use App\Models\Campeonato;
use App\Models\Time;
use App\Services\ChaveamentoCampeonatoService;

beforeEach(function () {
    $this->campeonato = Campeonato::create(['nome' => 'Campeonato Teste']);
    $this->service = new ChaveamentoCampeonatoService();

    // Cria 8 times e inscreve no campeonato
    for ($i = 1; $i <= 8; $i++) {
        $time = Time::create(['nome' => "Time {$i}"]);
        $this->campeonato->times()->attach($time->id, [
            'ordem_inscricao'   => $i,
            'pontuacao_total'   => 0,
            'gols_fora_de_casa' => 0,
        ]);
    }
});

it('quartas de final geram exatamente 4 partidas', function () {
    $this->service->gerarQuartasDeFinal($this->campeonato);

    expect($this->campeonato->partidas()->where('fase', 'quartas_de_final')->count())->toBe(4);
});

it('quartas de final usam todos os 8 times sem repetir', function () {
    $this->service->gerarQuartasDeFinal($this->campeonato);

    $partidas = $this->campeonato->partidas()->where('fase', 'quartas_de_final')->get();

    $timesUsados = $partidas->flatMap(fn ($p) => [$p->time_mandante_id, $p->time_visitante_id]);

    expect($timesUsados->count())->toBe(8)
        ->and($timesUsados->unique()->count())->toBe(8);
});

it('semifinais geram exatamente 2 partidas com vencedores das quartas', function () {
    $this->service->gerarQuartasDeFinal($this->campeonato);

    // Define vencedores das quartas
    $this->campeonato->partidas()->where('fase', 'quartas_de_final')->get()
        ->each(function ($partida) {
            $partida->update(['time_vencedor_id' => $partida->time_mandante_id]);
        });

    $this->service->gerarSemifinais($this->campeonato);

    $semifinais = $this->campeonato->partidas()->where('fase', 'semifinal')->get();
    $vencedoresQuartas = $this->campeonato->partidas()
        ->where('fase', 'quartas_de_final')
        ->pluck('time_mandante_id')
        ->toArray();

    expect($semifinais->count())->toBe(2);

    $semifinais->each(function ($partida) use ($vencedoresQuartas) {
        expect(in_array($partida->time_mandante_id, $vencedoresQuartas))->toBeTrue()
            ->and(in_array($partida->time_visitante_id, $vencedoresQuartas))->toBeTrue();
    });
});

it('terceiro lugar usa os perdedores das semifinais', function () {
    $this->service->gerarQuartasDeFinal($this->campeonato);

    $this->campeonato->partidas()->where('fase', 'quartas_de_final')->get()
        ->each(fn ($p) => $p->update(['time_vencedor_id' => $p->time_mandante_id]));

    $this->service->gerarSemifinais($this->campeonato);

    $this->campeonato->partidas()->where('fase', 'semifinal')->get()
        ->each(fn ($p) => $p->update(['time_vencedor_id' => $p->time_mandante_id]));

    $this->service->gerarTerceiroLugar($this->campeonato);

    $terceiroLugar = $this->campeonato->partidas()->where('fase', 'terceiro_lugar')->first();
    $perdedoresSemis = $this->campeonato->partidas()
        ->where('fase', 'semifinal')
        ->pluck('time_visitante_id')
        ->toArray();

    expect($terceiroLugar)->not->toBeNull()
        ->and(in_array($terceiroLugar->time_mandante_id, $perdedoresSemis))->toBeTrue()
        ->and(in_array($terceiroLugar->time_visitante_id, $perdedoresSemis))->toBeTrue();
});

it('final usa os vencedores das semifinais', function () {
    $this->service->gerarQuartasDeFinal($this->campeonato);

    $this->campeonato->partidas()->where('fase', 'quartas_de_final')->get()
        ->each(fn ($p) => $p->update(['time_vencedor_id' => $p->time_mandante_id]));

    $this->service->gerarSemifinais($this->campeonato);

    $this->campeonato->partidas()->where('fase', 'semifinal')->get()
        ->each(fn ($p) => $p->update(['time_vencedor_id' => $p->time_mandante_id]));

    $this->service->gerarFinal($this->campeonato);

    $final = $this->campeonato->partidas()->where('fase', 'final')->first();
    $vencedoresSemis = $this->campeonato->partidas()
        ->where('fase', 'semifinal')
        ->pluck('time_mandante_id')
        ->toArray();

    expect($final)->not->toBeNull()
        ->and(in_array($final->time_mandante_id, $vencedoresSemis))->toBeTrue()
        ->and(in_array($final->time_visitante_id, $vencedoresSemis))->toBeTrue();
});

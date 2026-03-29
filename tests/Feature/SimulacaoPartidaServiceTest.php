<?php

use App\Models\Campeonato;
use App\Models\Partida;
use App\Models\Time;
use App\Services\PlacarPythonService;
use App\Services\SimulacaoPartidaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->campeonato = Campeonato::create(['nome' => 'Campeonato Teste']);

    $this->timeMandante = Time::create(['nome' => 'Time A']);
    $this->timeVisitante = Time::create(['nome' => 'Time B']);

    $this->campeonato->times()->attach($this->timeMandante->id, [
        'ordem_inscricao'   => 1,
        'pontuacao_total'   => 0,
        'gols_fora_de_casa' => 0,
    ]);

    $this->campeonato->times()->attach($this->timeVisitante->id, [
        'ordem_inscricao'   => 2,
        'pontuacao_total'   => 0,
        'gols_fora_de_casa' => 0,
    ]);

    $this->partida = Partida::create([
        'campeonato_id'     => $this->campeonato->id,
        'fase'              => 'quartas_de_final',
        'time_mandante_id'  => $this->timeMandante->id,
        'time_visitante_id' => $this->timeVisitante->id,
    ]);
});

it('time com mais gols vence', function () {
    $mock = Mockery::mock(PlacarPythonService::class);
    $mock->shouldReceive('obterPlacar')->once()->andReturn([
        'gols_mandante'  => 3,
        'gols_visitante' => 1,
    ]);

    $service = new SimulacaoPartidaService($mock);
    $service->simular($this->partida);

    $this->partida->refresh();

    expect($this->partida->gols_mandante)->toBe(3)
        ->and($this->partida->gols_visitante)->toBe(1)
        ->and($this->partida->time_vencedor_id)->toBe($this->timeMandante->id);
});

it('empate: vence quem tem maior pontuacao_total', function () {
    $this->campeonato->times()->updateExistingPivot($this->timeMandante->id, ['pontuacao_total' => 5]);
    $this->campeonato->times()->updateExistingPivot($this->timeVisitante->id, ['pontuacao_total' => 10]);

    $mock = Mockery::mock(PlacarPythonService::class);
    $mock->shouldReceive('obterPlacar')->once()->andReturn([
        'gols_mandante'  => 2,
        'gols_visitante' => 2,
    ]);

    $service = new SimulacaoPartidaService($mock);
    $service->simular($this->partida);

    $this->partida->refresh();

    expect($this->partida->time_vencedor_id)->toBe($this->timeVisitante->id);
});

it('empate de pontuacao: vence quem tem mais gols fora de casa', function () {
    $this->campeonato->times()->updateExistingPivot($this->timeMandante->id, [
        'pontuacao_total'   => 5,
        'gols_fora_de_casa' => 2,
    ]);
    $this->campeonato->times()->updateExistingPivot($this->timeVisitante->id, [
        'pontuacao_total'   => 5,
        'gols_fora_de_casa' => 8,
    ]);

    $mock = Mockery::mock(PlacarPythonService::class);
    $mock->shouldReceive('obterPlacar')->once()->andReturn([
        'gols_mandante'  => 1,
        'gols_visitante' => 1,
    ]);

    $service = new SimulacaoPartidaService($mock);
    $service->simular($this->partida);

    $this->partida->refresh();

    expect($this->partida->time_vencedor_id)->toBe($this->timeVisitante->id);
});

it('empate total: vence quem tem menor ordem de inscricao', function () {
    $this->campeonato->times()->updateExistingPivot($this->timeMandante->id, [
        'pontuacao_total'   => 5,
        'gols_fora_de_casa' => 3,
        'ordem_inscricao'   => 1,
    ]);
    $this->campeonato->times()->updateExistingPivot($this->timeVisitante->id, [
        'pontuacao_total'   => 5,
        'gols_fora_de_casa' => 3,
        'ordem_inscricao'   => 2,
    ]);

    $mock = Mockery::mock(PlacarPythonService::class);
    $mock->shouldReceive('obterPlacar')->once()->andReturn([
        'gols_mandante'  => 0,
        'gols_visitante' => 0,
    ]);

    $service = new SimulacaoPartidaService($mock);
    $service->simular($this->partida);

    $this->partida->refresh();

    expect($this->partida->time_vencedor_id)->toBe($this->timeMandante->id);
});

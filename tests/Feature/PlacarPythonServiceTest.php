<?php

use App\Services\PlacarPythonService;

it('retorna placar correto a partir do output do python', function () {
    $service = Mockery::mock(PlacarPythonService::class)->makePartial();
    $service->shouldReceive('obterPlacar')->andReturn([
        'gols_mandante'  => 3,
        'gols_visitante' => 1,
    ]);

    $placar = $service->obterPlacar();

    expect($placar['gols_mandante'])->toBe(3)
        ->and($placar['gols_visitante'])->toBe(1);
});

it('nao remove zero do placar', function () {
    $service = Mockery::mock(PlacarPythonService::class)->makePartial();
    $service->shouldReceive('obterPlacar')->andReturn([
        'gols_mandante'  => 5,
        'gols_visitante' => 0,
    ]);

    $placar = $service->obterPlacar();

    expect($placar['gols_mandante'])->toBe(5)
        ->and($placar['gols_visitante'])->toBe(0);
});

it('lanca excecao em caso de falha na execucao do script', function () {
    $service = Mockery::mock(PlacarPythonService::class)->makePartial();
    $service->shouldReceive('obterPlacar')->andThrow(new RuntimeException('Falha ao executar o script Python.'));

    expect(fn () => $service->obterPlacar())->toThrow(RuntimeException::class);
});

<?php

use App\Models\Campeonato;
use App\Models\Time;
use App\Services\PlacarPythonService;

beforeEach(function () {
    $this->placarMock = Mockery::mock(PlacarPythonService::class);
    $this->placarMock->shouldReceive('obterPlacar')->andReturn([
        'gols_mandante'  => 2,
        'gols_visitante' => 1,
    ]);
    $this->app->instance(PlacarPythonService::class, $this->placarMock);
});

it('cria campeonato e retorna 201', function () {
    $response = $this->postJson('/api/campeonatos', ['nome' => 'Campeonato Teste']);

    $response->assertStatus(201)
        ->assertJsonPath('data.nome', 'Campeonato Teste')
        ->assertJsonPath('data.status', 'pendente');
});

it('retorna 422 ao criar campeonato sem nome', function () {
    $response = $this->postJson('/api/campeonatos', []);

    $response->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['nome']]);
});

it('inscreve time e retorna 201', function () {
    $campeonato = Campeonato::create(['nome' => 'Campeonato Teste']);

    $response = $this->postJson("/api/campeonatos/{$campeonato->id}/times", ['nome' => 'Flamengo']);

    $response->assertStatus(201)
        ->assertJsonPath('data.nome', 'Flamengo');
});

it('retorna 422 ao inscrever 9 time', function () {
    $campeonato = Campeonato::create(['nome' => 'Campeonato Teste']);

    for ($i = 1; $i <= 8; $i++) {
        $time = Time::create(['nome' => "Time {$i}"]);
        $campeonato->times()->attach($time->id, [
            'ordem_inscricao'   => $i,
            'pontuacao_total'   => 0,
            'gols_fora_de_casa' => 0,
        ]);
    }

    $response = $this->postJson("/api/campeonatos/{$campeonato->id}/times", ['nome' => 'Time 9']);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'O campeonato já possui 8 times inscritos.');
});

it('retorna 422 ao simular com menos de 8 times', function () {
    $campeonato = Campeonato::create(['nome' => 'Campeonato Teste']);

    for ($i = 1; $i <= 5; $i++) {
        $time = Time::create(['nome' => "Time {$i}"]);
        $campeonato->times()->attach($time->id, [
            'ordem_inscricao'   => $i,
            'pontuacao_total'   => 0,
            'gols_fora_de_casa' => 0,
        ]);
    }

    $response = $this->postJson("/api/campeonatos/{$campeonato->id}/simular");

    $response->assertStatus(422)
        ->assertJsonPath('message', 'O campeonato precisa ter exatamente 8 times inscritos para ser simulado.');
});

it('retorna 422 ao simular campeonato ja finalizado', function () {
    $campeonato = Campeonato::create(['nome' => 'Campeonato Teste', 'status' => 'finalizado']);

    $response = $this->postJson("/api/campeonatos/{$campeonato->id}/simular");

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Este campeonato já foi simulado.');
});

it('fluxo completo retorna 200 com status finalizado', function () {
    $campeonato = Campeonato::create(['nome' => 'Campeonato Teste']);

    for ($i = 1; $i <= 8; $i++) {
        $time = Time::create(['nome' => "Time {$i}"]);
        $campeonato->times()->attach($time->id, [
            'ordem_inscricao'   => $i,
            'pontuacao_total'   => 0,
            'gols_fora_de_casa' => 0,
        ]);
    }

    $response = $this->postJson("/api/campeonatos/{$campeonato->id}/simular");

    $response->assertStatus(200)
        ->assertJsonPath('campeonato.status', 'finalizado');
});

it('campeonato finalizado aparece na listagem', function () {
    $campeonato = Campeonato::create(['nome' => 'Campeonato Finalizado', 'status' => 'finalizado']);

    $response = $this->getJson('/api/campeonatos?status=finalizado');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.nome', 'Campeonato Finalizado')
        ->assertJsonPath('data.0.status', 'finalizado');
});

it('retorna 404 ao buscar campeonato inexistente', function () {
    $response = $this->getJson('/api/campeonatos/999');

    $response->assertStatus(404)
        ->assertJsonPath('message', 'Recurso não encontrado.');
});

it('detalha campeonato finalizado com classificacao', function () {
    $campeonato = Campeonato::create(['nome' => 'Campeonato Teste']);

    for ($i = 1; $i <= 8; $i++) {
        $time = Time::create(['nome' => "Time {$i}"]);
        $campeonato->times()->attach($time->id, [
            'ordem_inscricao'   => $i,
            'pontuacao_total'   => 0,
            'gols_fora_de_casa' => 0,
        ]);
    }

    $this->postJson("/api/campeonatos/{$campeonato->id}/simular");

    $response = $this->getJson("/api/campeonatos/{$campeonato->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'nome',
                'status',
                'times',
                'partidas',
                'classificacao',
            ],
        ]);
});

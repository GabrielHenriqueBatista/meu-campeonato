<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CriarCampeonatoRequest;
use App\Http\Requests\InscricaoTimeRequest;
use App\Http\Resources\CampeonatoResource;
use App\Http\Resources\TimeResource;
use App\Models\Campeonato;
use App\Models\Time;

class CampeonatoController extends Controller
{
    public function store(CriarCampeonatoRequest $request): \Illuminate\Http\JsonResponse
    {
        $campeonato = Campeonato::create($request->validated());

        return (new CampeonatoResource($campeonato->fresh()))
            ->response()
            ->setStatusCode(201);
    }
    public function inscreverTime(InscricaoTimeRequest $request, Campeonato $campeonato): \Illuminate\Http\JsonResponse
    {
        if (!$campeonato->isPendente()) {
            return response()->json([
                'message' => 'Não é possível inscrever times em um campeonato que não está pendente.',
            ], 422);
        }

        if ($campeonato->times()->count() >= 8) {
            return response()->json([
                'message' => 'O campeonato já possui 8 times inscritos.',
            ], 422);
        }

        $time = Time::create($request->validated());

        $ordemInscricao = $campeonato->times()->count() + 1;

        $campeonato->times()->attach($time->id, [
            'ordem_inscricao'   => $ordemInscricao,
            'pontuacao_total'   => 0,
            'gols_fora_de_casa' => 0,
        ]);

        return (new TimeResource($time))
            ->response()
            ->setStatusCode(201);
    }
}

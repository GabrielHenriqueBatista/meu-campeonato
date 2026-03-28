<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CriarCampeonatoRequest;
use App\Http\Resources\CampeonatoResource;
use App\Models\Campeonato;

class CampeonatoController extends Controller
{
    public function store(CriarCampeonatoRequest $request): \Illuminate\Http\JsonResponse
    {
        $campeonato = Campeonato::create($request->validated());

        return (new CampeonatoResource($campeonato->fresh()))
            ->response()
            ->setStatusCode(201);
    }
}

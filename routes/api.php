<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CampeonatoController;

Route::post('/campeonatos', [CampeonatoController::class, 'store']);
Route::post('/campeonatos/{campeonato}/times', [CampeonatoController::class, 'inscreverTime']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

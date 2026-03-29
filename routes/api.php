<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CampeonatoController;

Route::post('/campeonatos', [CampeonatoController::class, 'store']);
Route::post('/campeonatos/{campeonato}/times', [CampeonatoController::class, 'inscreverTime']);
Route::post('/campeonatos/{campeonato}/simular', [CampeonatoController::class, 'simular']);
Route::get('/campeonatos', [CampeonatoController::class, 'index']);

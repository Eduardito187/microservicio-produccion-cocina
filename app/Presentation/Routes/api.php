<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\EventBusController;
use App\Presentation\Http\Controllers\GenerarOPController;
use App\Presentation\Http\Controllers\ProcesarOPController;
use App\Presentation\Http\Controllers\PlanificarOPController;
use App\Presentation\Http\Controllers\DespacharOPController;

Route::post('/produccion/ordenes/generar', GenerarOPController::class);
Route::post('/produccion/ordenes/planificar', PlanificarOPController::class);
Route::post('/produccion/ordenes/procesar', ProcesarOPController::class);
Route::post('/produccion/ordenes/despachar', DespacharOPController::class);

//api eventos
Route::post('/event-bus', EventBusController::class);
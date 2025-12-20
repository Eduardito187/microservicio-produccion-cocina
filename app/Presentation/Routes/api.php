<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\EventBusController;
use App\Presentation\Http\Controllers\PactStateController;
use App\Presentation\Http\Controllers\GenerarOPController;
use App\Presentation\Http\Controllers\ProcesarOPController;
use App\Presentation\Http\Controllers\DespacharOPController;
use App\Presentation\Http\Controllers\PlanificarOPController;

Route::post('/produccion/ordenes/generar', GenerarOPController::class)->name('produccion.ordenes.generar');
Route::post('/produccion/ordenes/planificar', PlanificarOPController::class)->name('produccion.ordenes.planificar');
Route::post('/produccion/ordenes/procesar', ProcesarOPController::class)->name('produccion.ordenes.procesar');
Route::post('/produccion/ordenes/despachar', DespacharOPController::class)->name('produccion.ordenes.despachar');

// api eventos
Route::post('/event-bus', EventBusController::class);

// test
Route::post('/_pact/setup', PactStateController::class);
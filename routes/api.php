<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Infrastructure\Http\Controllers\EventBusController;
use App\Infrastructure\Http\Controllers\GenerarOPController;
use App\Infrastructure\Http\Controllers\ProcesarOPController;
use App\Infrastructure\Http\Controllers\PlanificarOPController;
use App\Infrastructure\Http\Controllers\DespacharOPController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/produccion/ordenes/generar', GenerarOPController::class);
Route::post('/produccion/ordenes/planificar', PlanificarOPController::class);
Route::post('/produccion/ordenes/procesar', ProcesarOPController::class);
Route::post('/produccion/ordenes/despachar', DespacharOPController::class);

Route::post('/event-bus', EventBusController::class);
<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\EventBusController;
use App\Presentation\Http\Controllers\PactStateController;
use App\Presentation\Http\Controllers\GenerarOPController;
use App\Presentation\Http\Controllers\ProcesarOPController;
use App\Presentation\Http\Controllers\DespacharOPController;
use App\Presentation\Http\Controllers\PlanificarOPController;
use App\Presentation\Http\Controllers\CrearPacienteController;
use App\Presentation\Http\Controllers\ActualizarPacienteController;
use App\Presentation\Http\Controllers\EliminarPacienteController;
use App\Presentation\Http\Controllers\CrearDireccionController;
use App\Presentation\Http\Controllers\ActualizarDireccionController;
use App\Presentation\Http\Controllers\EliminarDireccionController;
use App\Presentation\Http\Controllers\CrearVentanaEntregaController;
use App\Presentation\Http\Controllers\ActualizarVentanaEntregaController;
use App\Presentation\Http\Controllers\EliminarVentanaEntregaController;

Route::post('/produccion/ordenes/generar', GenerarOPController::class)->name('produccion.ordenes.generar');
Route::post('/produccion/ordenes/planificar', PlanificarOPController::class)->name('produccion.ordenes.planificar');
Route::post('/produccion/ordenes/procesar', ProcesarOPController::class)->name('produccion.ordenes.procesar');
Route::post('/produccion/ordenes/despachar', DespacharOPController::class)->name('produccion.ordenes.despachar');

Route::post('/pacientes', CrearPacienteController::class)->name('pacientes.crear');
Route::put('/pacientes/{id}', ActualizarPacienteController::class)->name('pacientes.actualizar');
Route::delete('/pacientes/{id}', EliminarPacienteController::class)->name('pacientes.eliminar');

Route::post('/direcciones', CrearDireccionController::class)->name('direcciones.crear');
Route::put('/direcciones/{id}', ActualizarDireccionController::class)->name('direcciones.actualizar');
Route::delete('/direcciones/{id}', EliminarDireccionController::class)->name('direcciones.eliminar');

Route::post('/ventanas-entrega', CrearVentanaEntregaController::class)->name('ventanas-entrega.crear');
Route::put('/ventanas-entrega/{id}', ActualizarVentanaEntregaController::class)->name('ventanas-entrega.actualizar');
Route::delete('/ventanas-entrega/{id}', EliminarVentanaEntregaController::class)->name('ventanas-entrega.eliminar');

// api eventos
Route::post('/event-bus', EventBusController::class);

// test
Route::post('/_pact/setup', PactStateController::class);

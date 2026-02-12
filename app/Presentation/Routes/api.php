<?php
/**
 * Microservicio "Produccion y Cocina"
 */

use App\Presentation\Http\Controllers\ActualizarCalendarioItemController;
use App\Presentation\Http\Controllers\ActualizarVentanaEntregaController;
use App\Presentation\Http\Controllers\ActualizarRecetaVersionController;
use App\Presentation\Http\Controllers\EliminarVentanaEntregaController;
use App\Presentation\Http\Controllers\EliminarCalendarioItemController;
use App\Presentation\Http\Controllers\ListarVentanasEntregaController;
use App\Presentation\Http\Controllers\ActualizarSuscripcionController;
use App\Presentation\Http\Controllers\EliminarRecetaVersionController;
use App\Presentation\Http\Controllers\ListarCalendarioItemsController;
use App\Presentation\Http\Controllers\ListarRecetasVersionController;
use App\Presentation\Http\Controllers\ActualizarCalendarioController;
use App\Presentation\Http\Controllers\CrearVentanaEntregaController;
use App\Presentation\Http\Controllers\ActualizarDireccionController;
use App\Presentation\Http\Controllers\CrearCalendarioItemController;
use App\Presentation\Http\Controllers\EliminarSuscripcionController;
use App\Presentation\Http\Controllers\ListarSuscripcionesController;
use App\Presentation\Http\Controllers\ActualizarPacienteController;
use App\Presentation\Http\Controllers\ActualizarEstacionController;
use App\Presentation\Http\Controllers\CrearRecetaVersionController;
use App\Presentation\Http\Controllers\EliminarCalendarioController;
use App\Presentation\Http\Controllers\ActualizarProductoController;
use App\Presentation\Http\Controllers\ActualizarEtiquetaController;
use App\Presentation\Http\Controllers\ListarDireccionesController;
use App\Presentation\Http\Controllers\VerVentanaEntregaController;
use App\Presentation\Http\Controllers\ActualizarPorcionController;
use App\Presentation\Http\Controllers\EliminarDireccionController;
use App\Presentation\Http\Controllers\ListarCalendariosController;
use App\Presentation\Http\Controllers\VerCalendarioItemController;
use App\Presentation\Http\Controllers\ActualizarPaqueteController;
use App\Presentation\Http\Controllers\EliminarPacienteController;
use App\Presentation\Http\Controllers\EliminarEstacionController;
use App\Presentation\Http\Controllers\ListarEstacionesController;
use App\Presentation\Http\Controllers\VerRecetaVersionController;
use App\Presentation\Http\Controllers\CrearSuscripcionController;
use App\Presentation\Http\Controllers\EliminarEtiquetaController;
use App\Presentation\Http\Controllers\CrearCalendarioController;
use App\Presentation\Http\Controllers\EliminarPorcionController;
use App\Presentation\Http\Controllers\ListarPorcionesController;
use App\Presentation\Http\Controllers\ListarPacientesController;
use App\Presentation\Http\Controllers\ListarEtiquetasController;
use App\Presentation\Http\Controllers\EliminarPaqueteController;
use App\Presentation\Http\Controllers\ListarProductosController;
use App\Presentation\Http\Controllers\CrearDireccionController;
use App\Presentation\Http\Controllers\VerSuscripcionController;
use App\Presentation\Http\Controllers\ListarPaquetesController;
use App\Presentation\Http\Controllers\VerCalendarioController;
use App\Presentation\Http\Controllers\CrearPacienteController;
use App\Presentation\Http\Controllers\CrearEstacionController;
use App\Presentation\Http\Controllers\CrearEtiquetaController;
use App\Presentation\Http\Controllers\CrearProductoController;
use App\Presentation\Http\Controllers\CrearPaqueteController;
use App\Presentation\Http\Controllers\PlanificarOPController;
use App\Presentation\Http\Controllers\CrearPorcionController;
use App\Presentation\Http\Controllers\VerDireccionController;
use App\Presentation\Http\Controllers\DespacharOPController;
use App\Presentation\Http\Controllers\VerPacienteController;
use App\Presentation\Http\Controllers\VerEstacionController;
use App\Presentation\Http\Controllers\VerEtiquetaController;
use App\Presentation\Http\Controllers\VerProductoController;
use App\Presentation\Http\Controllers\VerPaqueteController;
use App\Presentation\Http\Controllers\ProcesarOPController;
use App\Presentation\Http\Controllers\VerPorcionController;
use App\Presentation\Http\Controllers\GenerarOPController;
use App\Presentation\Http\Controllers\PactStateController;
use App\Presentation\Http\Controllers\EventBusController;
use App\Presentation\Http\Controllers\RefreshController;
use App\Presentation\Http\Controllers\LoginController;
use App\Presentation\Http\Controllers\ProxyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['keycloak.jwt', 'role:cocinero,planificador,despachador,produccion'])->group(function () {
    Route::post('/produccion/ordenes/generar', GenerarOPController::class)
        ->middleware('role:planificador')
        ->name('produccion.ordenes.generar');
    Route::post('/produccion/ordenes/planificar', PlanificarOPController::class)
        ->middleware('role:planificador')
        ->name('produccion.ordenes.planificar');
    Route::post('/produccion/ordenes/procesar', ProcesarOPController::class)
        ->middleware('role:cocinero,produccion')
        ->name('produccion.ordenes.procesar');
    Route::post('/produccion/ordenes/despachar', DespacharOPController::class)
        ->middleware('role:despachador')
        ->name('produccion.ordenes.despachar');

    Route::post('/pacientes', CrearPacienteController::class)->middleware('role:planificador,produccion')->name('pacientes.crear');
    Route::get('/pacientes', ListarPacientesController::class)->name('pacientes.listar');
    Route::get('/pacientes/{id}', VerPacienteController::class)->name('pacientes.ver');
    Route::put('/pacientes/{id}', ActualizarPacienteController::class)->middleware('role:planificador,produccion')->name('pacientes.actualizar');
    Route::delete('/pacientes/{id}', EliminarPacienteController::class)->middleware('role:planificador,produccion')->name('pacientes.eliminar');

    Route::post('/direcciones', CrearDireccionController::class)->middleware('role:planificador,produccion')->name('direcciones.crear');
    Route::get('/direcciones', ListarDireccionesController::class)->name('direcciones.listar');
    Route::get('/direcciones/{id}', VerDireccionController::class)->name('direcciones.ver');
    Route::put('/direcciones/{id}', ActualizarDireccionController::class)->middleware('role:planificador,produccion')->name('direcciones.actualizar');
    Route::delete('/direcciones/{id}', EliminarDireccionController::class)->middleware('role:planificador,produccion')->name('direcciones.eliminar');

    Route::post('/ventanas-entrega', CrearVentanaEntregaController::class)->middleware('role:planificador,produccion')->name('ventanas-entrega.crear');
    Route::get('/ventanas-entrega', ListarVentanasEntregaController::class)->name('ventanas-entrega.listar');
    Route::get('/ventanas-entrega/{id}', VerVentanaEntregaController::class)->name('ventanas-entrega.ver');
    Route::put('/ventanas-entrega/{id}', ActualizarVentanaEntregaController::class)->middleware('role:planificador,produccion')->name('ventanas-entrega.actualizar');
    Route::delete('/ventanas-entrega/{id}', EliminarVentanaEntregaController::class)->middleware('role:planificador,produccion')->name('ventanas-entrega.eliminar');

    Route::post('/estaciones', CrearEstacionController::class)->middleware('role:planificador,produccion')->name('estaciones.crear');
    Route::get('/estaciones', ListarEstacionesController::class)->name('estaciones.listar');
    Route::get('/estaciones/{id}', VerEstacionController::class)->name('estaciones.ver');
    Route::put('/estaciones/{id}', ActualizarEstacionController::class)->middleware('role:planificador,produccion')->name('estaciones.actualizar');
    Route::delete('/estaciones/{id}', EliminarEstacionController::class)->middleware('role:planificador,produccion')->name('estaciones.eliminar');

    Route::post('/porciones', CrearPorcionController::class)->middleware('role:planificador,produccion')->name('porciones.crear');
    Route::get('/porciones', ListarPorcionesController::class)->name('porciones.listar');
    Route::get('/porciones/{id}', VerPorcionController::class)->name('porciones.ver');
    Route::put('/porciones/{id}', ActualizarPorcionController::class)->middleware('role:planificador,produccion')->name('porciones.actualizar');
    Route::delete('/porciones/{id}', EliminarPorcionController::class)->middleware('role:planificador,produccion')->name('porciones.eliminar');

    Route::post('/recetas-version', CrearRecetaVersionController::class)->middleware('role:planificador,produccion')->name('recetas-version.crear');
    Route::get('/recetas-version', ListarRecetasVersionController::class)->name('recetas-version.listar');
    Route::get('/recetas-version/{id}', VerRecetaVersionController::class)->name('recetas-version.ver');
    Route::put('/recetas-version/{id}', ActualizarRecetaVersionController::class)->middleware('role:planificador,produccion')->name('recetas-version.actualizar');
    Route::delete('/recetas-version/{id}', EliminarRecetaVersionController::class)->middleware('role:planificador,produccion')->name('recetas-version.eliminar');

    Route::post('/suscripciones', CrearSuscripcionController::class)->middleware('role:planificador,produccion')->name('suscripciones.crear');
    Route::get('/suscripciones', ListarSuscripcionesController::class)->name('suscripciones.listar');
    Route::get('/suscripciones/{id}', VerSuscripcionController::class)->name('suscripciones.ver');
    Route::put('/suscripciones/{id}', ActualizarSuscripcionController::class)->middleware('role:planificador,produccion')->name('suscripciones.actualizar');
    Route::delete('/suscripciones/{id}', EliminarSuscripcionController::class)->middleware('role:planificador,produccion')->name('suscripciones.eliminar');

    Route::post('/productos', CrearProductoController::class)->middleware('role:planificador,produccion')->name('productos.crear');
    Route::get('/productos', ListarProductosController::class)->name('productos.listar');
    Route::get('/productos/{id}', VerProductoController::class)->name('productos.ver');
    Route::put('/productos/{id}', ActualizarProductoController::class)->middleware('role:planificador,produccion')->name('productos.actualizar');

    Route::post('/calendarios', CrearCalendarioController::class)->middleware('role:planificador,produccion')->name('calendarios.crear');
    Route::get('/calendarios', ListarCalendariosController::class)->name('calendarios.listar');
    Route::get('/calendarios/{id}', VerCalendarioController::class)->name('calendarios.ver');
    Route::put('/calendarios/{id}', ActualizarCalendarioController::class)->middleware('role:planificador,produccion')->name('calendarios.actualizar');
    Route::delete('/calendarios/{id}', EliminarCalendarioController::class)->middleware('role:planificador,produccion')->name('calendarios.eliminar');

    Route::post('/calendario-items', CrearCalendarioItemController::class)->middleware('role:planificador,produccion')->name('calendario-items.crear');
    Route::get('/calendario-items', ListarCalendarioItemsController::class)->name('calendario-items.listar');
    Route::get('/calendario-items/{id}', VerCalendarioItemController::class)->name('calendario-items.ver');
    Route::put('/calendario-items/{id}', ActualizarCalendarioItemController::class)->middleware('role:planificador,produccion')->name('calendario-items.actualizar');
    Route::delete('/calendario-items/{id}', EliminarCalendarioItemController::class)->middleware('role:planificador,produccion')->name('calendario-items.eliminar');

    Route::post('/etiquetas', CrearEtiquetaController::class)->middleware('role:planificador,produccion')->name('etiquetas.crear');
    Route::get('/etiquetas', ListarEtiquetasController::class)->name('etiquetas.listar');
    Route::get('/etiquetas/{id}', VerEtiquetaController::class)->name('etiquetas.ver');
    Route::put('/etiquetas/{id}', ActualizarEtiquetaController::class)->middleware('role:planificador,produccion')->name('etiquetas.actualizar');
    Route::delete('/etiquetas/{id}', EliminarEtiquetaController::class)->middleware('role:planificador,produccion')->name('etiquetas.eliminar');

    Route::post('/paquetes', CrearPaqueteController::class)->middleware('role:planificador,produccion')->name('paquetes.crear');
    Route::get('/paquetes', ListarPaquetesController::class)->name('paquetes.listar');
    Route::get('/paquetes/{id}', VerPaqueteController::class)->name('paquetes.ver');
    Route::put('/paquetes/{id}', ActualizarPaqueteController::class)->middleware('role:planificador,produccion')->name('paquetes.actualizar');
    Route::delete('/paquetes/{id}', EliminarPaqueteController::class)->middleware('role:planificador,produccion')->name('paquetes.eliminar');

    // api eventos
    Route::post('/event-bus', EventBusController::class)->middleware('role:produccion');

    //API Gateway
    Route::get('/users', [ProxyController::class, 'users']);
    Route::get('/posts', [ProxyController::class, 'posts']);
});

// test
Route::post('/_pact/setup', PactStateController::class);

//KeyCloak
Route::post('/login', LoginController::class);
Route::post('/refresh', RefreshController::class);
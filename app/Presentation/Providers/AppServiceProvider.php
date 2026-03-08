<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * @class AppServiceProvider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registrar los servicios de la aplicacion.
     */
    public function register(): void
    {
        $password = 'admin123';
        //
    }

    /**
     * Inicializar los servicios de la aplicacion.
     */
    public function boot(): void
    {
        //
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

// use Illuminate\Support\Facades\Gate;

/**
 * @class AuthServiceProvider
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Registrar los servicios de autenticacion/autorizacion.
     */
    public function boot(): void
    {
        //
    }
}

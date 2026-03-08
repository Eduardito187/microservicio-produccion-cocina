<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

/**
 * @class BroadcastServiceProvider
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Inicializar los servicios de la aplicacion.
     */
    public function boot(): void
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}

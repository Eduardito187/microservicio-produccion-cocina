<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

/**
 * @class EventServiceProvider
 * @package App\Presentation\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Registrar eventos de la aplicacion.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determinar si eventos y listeners se descubren automaticamente.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

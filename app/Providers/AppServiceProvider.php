<?php

namespace App\Providers;

use App\Infrastructure\Persistence\Eloquent\Repository\OrdenProduccionRepository;
use App\Domain\Produccion\Repository\OrdenProduccion;
use App\Infrastructure\EventBus\HttpEventBus;
use Illuminate\Support\ServiceProvider;
use App\Application\Shared\EventBus;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            OrdenProduccion::class,
            OrdenProduccionRepository::class
        );

        $this->app->bind(
            EventBus::class,
            HttpEventBus::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

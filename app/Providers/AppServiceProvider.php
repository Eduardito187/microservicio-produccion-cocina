<?php

namespace App\Providers;

use App\Infrastructure\Persistence\Eloquent\Repository\OrdenProduccionRepository;
use App\Infrastructure\Persistence\Eloquent\Repository\ProduccionBatchRepository;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Application\Shared\EventBusInterface;
use App\Infrastructure\EventBus\HttpEventBus;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            OrdenProduccionRepositoryInterface::class,
            OrdenProduccionRepository::class
        );

        $this->app->bind(
            ProduccionBatchRepositoryInterface::class,
            ProduccionBatchRepository::class
        );

        $this->app->bind(
            EventBusInterface::class,
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

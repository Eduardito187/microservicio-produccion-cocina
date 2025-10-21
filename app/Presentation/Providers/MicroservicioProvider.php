<?php

namespace App\Presentation\Providers;

use App\Infrastructure\Persistence\Repository\OrdenProduccionRepository;
use App\Infrastructure\Persistence\Repository\ProduccionBatchRepository;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Application\Shared\BusInterface;
use App\Infrastructure\Bus\HttpEventBus;
use Illuminate\Support\ServiceProvider;

class MicroservicioProvider extends ServiceProvider
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
            BusInterface::class,
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
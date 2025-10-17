<?php

namespace App\Providers;

use App\Infrastructure\Persistence\Eloquent\Repository\OrdenProduccionRepository;
use App\Domain\Produccion\Repository\OrdenProduccion;
use Illuminate\Support\ServiceProvider;

class ProductionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            OrdenProduccion::class,
            OrdenProduccionRepository::class
        );
    }
}
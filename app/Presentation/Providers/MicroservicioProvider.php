<?php

namespace App\Presentation\Providers;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Infrastructure\Persistence\Repository\OrdenProduccionRepository;
use App\Infrastructure\Persistence\Repository\ProduccionBatchRepository;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Infrastructure\Persistence\Transaction\TransactionManager;
use App\Application\Shared\BusInterface;
use App\Infrastructure\Bus\HttpEventBus;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

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

        $this->app->bind(
            TransactionManagerInterface::class,
            TransactionManager::class
        );

        Route::middleware('api')->prefix('api')->group(app_path('Presentation/Routes/api.php'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
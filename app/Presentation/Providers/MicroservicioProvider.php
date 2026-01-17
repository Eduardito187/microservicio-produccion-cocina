<?php

namespace App\Presentation\Providers;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Infrastructure\Persistence\Repository\OrdenProduccionRepository;
use App\Infrastructure\Persistence\Repository\ProduccionBatchRepository;
use App\Infrastructure\Persistence\Repository\PacienteRepository;
use App\Infrastructure\Persistence\Repository\DireccionRepository;
use App\Infrastructure\Persistence\Repository\VentanaEntregaRepository;
use App\Infrastructure\Persistence\Repository\EstacionRepository;
use App\Infrastructure\Persistence\Repository\PorcionRepository;
use App\Infrastructure\Persistence\Repository\RecetaVersionRepository;
use App\Infrastructure\Persistence\Repository\SuscripcionRepository;
use App\Infrastructure\Persistence\Repository\CalendarioRepository;
use App\Infrastructure\Persistence\Repository\CalendarioItemRepository;
use App\Infrastructure\Persistence\Repository\EtiquetaRepository;
use App\Infrastructure\Persistence\Repository\PaqueteRepository;
use App\Infrastructure\Persistence\Repository\ProductRepository;
use App\Infrastructure\Persistence\Repository\InboundEventRepository;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Domain\Produccion\Repository\EstacionRepositoryInterface;
use App\Domain\Produccion\Repository\PorcionRepositoryInterface;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Domain\Produccion\Repository\ProductRepositoryInterface;
use App\Domain\Produccion\Repository\InboundEventRepositoryInterface;
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
            PacienteRepositoryInterface::class,
            PacienteRepository::class
        );

        $this->app->bind(
            DireccionRepositoryInterface::class,
            DireccionRepository::class
        );

        $this->app->bind(
            VentanaEntregaRepositoryInterface::class,
            VentanaEntregaRepository::class
        );

        $this->app->bind(
            EstacionRepositoryInterface::class,
            EstacionRepository::class
        );

        $this->app->bind(
            PorcionRepositoryInterface::class,
            PorcionRepository::class
        );

        $this->app->bind(
            RecetaVersionRepositoryInterface::class,
            RecetaVersionRepository::class
        );

        $this->app->bind(
            SuscripcionRepositoryInterface::class,
            SuscripcionRepository::class
        );

        $this->app->bind(
            CalendarioRepositoryInterface::class,
            CalendarioRepository::class
        );

        $this->app->bind(
            CalendarioItemRepositoryInterface::class,
            CalendarioItemRepository::class
        );

        $this->app->bind(
            EtiquetaRepositoryInterface::class,
            EtiquetaRepository::class
        );

        $this->app->bind(
            PaqueteRepositoryInterface::class,
            PaqueteRepository::class
        );

        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );

        $this->app->bind(
            InboundEventRepositoryInterface::class,
            InboundEventRepository::class
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




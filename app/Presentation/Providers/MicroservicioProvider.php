<?php
/**
 * Microservicio "Produccion y Cocina"
 */

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
use App\Infrastructure\Persistence\Repository\ItemDespachoRepository;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Shared\OutboxStoreInterface;
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
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Infrastructure\Persistence\Transaction\TransactionManager;
use App\Infrastructure\Persistence\Outbox\OutboxEventPublisher;
use App\Infrastructure\Persistence\Outbox\OutboxStoreAdapter;
use App\Application\Shared\BusInterface;
use App\Infrastructure\Bus\HttpEventBus;
use App\Infrastructure\Bus\RabbitMqEventBus;
use App\Application\Integration\IntegrationEventRouter;
use App\Application\Integration\Handlers\DireccionCreadaHandler;
use App\Application\Integration\Handlers\DireccionActualizadaHandler;
use App\Application\Integration\Handlers\DireccionGeocodificadaHandler;
use App\Application\Integration\Handlers\PacienteCreadoHandler;
use App\Application\Integration\Handlers\PacienteActualizadoHandler;
use App\Application\Integration\Handlers\SuscripcionCreadaHandler;
use App\Application\Integration\Handlers\SuscripcionActualizadaHandler;
use App\Application\Integration\Handlers\RecetaActualizadaHandler;
use App\Application\Integration\Handlers\CalendarioEntregaCreadoHandler;
use App\Application\Integration\Handlers\EntregaProgramadaHandler;
use App\Application\Integration\Handlers\DiaSinEntregaMarcadoHandler;
use App\Application\Integration\Handlers\DireccionEntregaCambiadaHandler;
use App\Application\Integration\Handlers\EntregaConfirmadaHandler;
use App\Application\Integration\Handlers\EntregaFallidaHandler;
use App\Application\Integration\Handlers\PaqueteEnRutaHandler;
use App\Application\Analytics\KpiRepositoryInterface;
use App\Infrastructure\Persistence\Repository\KpiRepository;
use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Infrastructure\Persistence\Repository\EntregaEvidenciaRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * @class MicroservicioProvider
 * @package App\Presentation\Providers
 */
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
            ItemDespachoRepositoryInterface::class,
            ItemDespachoRepository::class
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
            KpiRepositoryInterface::class,
            KpiRepository::class
        );

        $this->app->bind(
            EntregaEvidenciaRepositoryInterface::class,
            EntregaEvidenciaRepository::class
        );

        $this->app->bind(
            OutboxStoreInterface::class,
            OutboxStoreAdapter::class
        );

        $this->app->bind(
            DomainEventPublisherInterface::class,
            OutboxEventPublisher::class
        );

        $this->app->bind(BusInterface::class, function () {
            $driver = env('EVENTBUS_DRIVER', 'http');
            return $driver === 'rabbitmq' ? new RabbitMqEventBus() : new HttpEventBus();
        });

        $this->app->singleton(IntegrationEventRouter::class, function ($app) {
            $router = new IntegrationEventRouter();

            $router->register('DireccionCreada', $app->make(DireccionCreadaHandler::class));
            $router->register('DireccionActualizada', $app->make(DireccionActualizadaHandler::class));
            $router->register('DireccionGeocodificada', $app->make(DireccionGeocodificadaHandler::class));

            $router->register('PacienteCreado', $app->make(PacienteCreadoHandler::class));
            $router->register('PacienteActualizado', $app->make(PacienteActualizadoHandler::class));

            $router->register('SuscripcionCreada', $app->make(SuscripcionCreadaHandler::class));
            $router->register('SuscripcionActualizada', $app->make(SuscripcionActualizadaHandler::class));

            $router->register('RecetaActualizada', $app->make(RecetaActualizadaHandler::class));

            $router->register('CalendarioEntregaCreado', $app->make(CalendarioEntregaCreadoHandler::class));
            $router->register('EntregaProgramada', $app->make(EntregaProgramadaHandler::class));
            $router->register('DiaSinEntregaMarcado', $app->make(DiaSinEntregaMarcadoHandler::class));
            $router->register('DireccionEntregaCambiada', $app->make(DireccionEntregaCambiadaHandler::class));

            $router->register('EntregaConfirmada', $app->make(EntregaConfirmadaHandler::class));
            $router->register('EntregaFallida', $app->make(EntregaFallidaHandler::class));
            $router->register('PaqueteEnRuta', $app->make(PaqueteEnRutaHandler::class));

            return $router;
        });

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

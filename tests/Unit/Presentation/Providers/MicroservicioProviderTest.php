<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation\Providers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\IntegrationEventRouter;
use App\Application\Shared\BusInterface;
use App\Presentation\Providers\MicroservicioProvider;
use Tests\TestCase;

/**
 * @class MicroservicioProviderTest
 */
class MicroservicioProviderTest extends TestCase
{
    public function test_register_binds_bus_driver_by_env(): void
    {
        $provider = new MicroservicioProvider($this->app);
        $provider->register();

        $bus = $this->app->make(BusInterface::class);

        $this->assertTrue(
            $bus instanceof \App\Infrastructure\Bus\HttpEventBus
            || $bus instanceof \App\Infrastructure\Bus\RabbitMqEventBus
        );
    }

    public function test_register_configures_integration_router_aliases(): void
    {
        $provider = new MicroservicioProvider($this->app);
        $provider->register();

        foreach ($this->integrationHandlers() as $handlerClass) {
            $this->app->bind($handlerClass, IntegrationHandlerStub::class);
        }

        IntegrationHandlerStub::$calls = [];
        $this->app->forgetInstance(IntegrationEventRouter::class);

        $router = $this->app->make(IntegrationEventRouter::class);

        $this->assertInstanceOf(IntegrationEventRouter::class, $router);

        $router->dispatch('paciente.paciente-creado', ['pacienteId' => 'p-1']);
        $router->dispatch('planes.receta-actualizada', ['id' => 'r-1']);
        $router->dispatch('logistica.paquete.estado-actualizado', ['packageId' => 'pkg-1', 'deliveryStatus' => 'ENTREGADO']);

        $this->assertCount(3, IntegrationHandlerStub::$calls);
    }

    /**
     * @return array<int, class-string>
     */
    private function integrationHandlers(): array
    {
        return [
            \App\Application\Integration\Handlers\DireccionCreadaHandler::class,
            \App\Application\Integration\Handlers\DireccionActualizadaHandler::class,
            \App\Application\Integration\Handlers\DireccionGeocodificadaHandler::class,
            \App\Application\Integration\Handlers\PacienteCreadoHandler::class,
            \App\Application\Integration\Handlers\PacienteActualizadoHandler::class,
            \App\Application\Integration\Handlers\PacienteEliminadoHandler::class,
            \App\Application\Integration\Handlers\RecetaActualizadaHandler::class,
            \App\Application\Integration\Handlers\SuscripcionCreadaHandler::class,
            \App\Application\Integration\Handlers\SuscripcionActualizadaHandler::class,
            \App\Application\Integration\Handlers\SuscripcionCrearHandler::class,
            \App\Application\Integration\Handlers\ContratoConsultarHandler::class,
            \App\Application\Integration\Handlers\ContratoCanceladoHandler::class,
            \App\Application\Integration\Handlers\CalendarioEntregaCreadoHandler::class,
            \App\Application\Integration\Handlers\CalendarioServicioGenerarHandler::class,
            \App\Application\Integration\Handlers\CalendarioGeneradoHandler::class,
            \App\Application\Integration\Handlers\EntregaProgramadaHandler::class,
            \App\Application\Integration\Handlers\DiaSinEntregaMarcadoHandler::class,
            \App\Application\Integration\Handlers\DireccionEntregaCambiadaHandler::class,
            \App\Application\Integration\Handlers\EntregaConfirmadaHandler::class,
            \App\Application\Integration\Handlers\EntregaFallidaHandler::class,
            \App\Application\Integration\Handlers\PaqueteEnRutaHandler::class,
            \App\Application\Integration\Handlers\LogisticaPaqueteEstadoActualizadoHandler::class,
        ];
    }
}

class IntegrationHandlerStub implements IntegrationEventHandlerInterface
{
    public static array $calls = [];

    public function handle(array $payload, array $meta = []): void
    {
        self::$calls[] = ['payload' => $payload, 'meta' => $meta];
    }
}

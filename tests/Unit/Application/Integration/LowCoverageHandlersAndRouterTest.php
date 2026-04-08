<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration;

use App\Application\Integration\CalendarProcessManager;
use App\Application\Integration\Handlers\CalendarioEntregaCreadoHandler;
use App\Application\Integration\Handlers\CalendarioGeneradoHandler;
use App\Application\Integration\Handlers\CalendarioServicioGenerarHandler;
use App\Application\Integration\Handlers\ContratoConsultarHandler;
use App\Application\Integration\Handlers\DireccionEntregaCambiadaHandler;
use App\Application\Integration\Handlers\DireccionGeocodificadaHandler;
use App\Application\Integration\Handlers\SuscripcionCrearHandler;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\IntegrationEventRouter;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Direccion;
use App\Domain\Produccion\Entity\Paquete;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Repository\CalendarioItemRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use Tests\TestCase;

/**
 * @class LowCoverageHandlersAndRouterTest
 */
class LowCoverageHandlersAndRouterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        $this->createSchema();
    }

    public function test_integration_event_router_register_y_dispatch_delega_handler(): void
    {
        $router = new IntegrationEventRouter;

        $handler = new class implements IntegrationEventHandlerInterface
        {
            public array $received = [];

            public function handle(array $payload, array $meta = []): void
            {
                $this->received = ['payload' => $payload, 'meta' => $meta];
            }
        };

        $router->register('evt.test', $handler);
        $router->dispatch('evt.test', ['x' => 1], ['event_id' => 'evt-1']);
        $this->assertSame(['x' => 1], $handler->received['payload']);
        $this->assertSame(['event_id' => 'evt-1'], $handler->received['meta']);
    }

    public function test_integration_event_router_evento_sin_handler_loguea_warning(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $router = new IntegrationEventRouter([], $logger);
        $router->dispatch('evt.missing', ['x' => 1], ['y' => 2]);

        $this->assertTrue(true);
    }

    public function test_calendario_entrega_creado_handler_crea_calendario_ventana_y_sincroniza_items(): void
    {
        DB::table('item_despacho')->insert([
            ['id' => 'item-1', 'entrega_id' => 'ent-1', 'contrato_id' => null],
            ['id' => 'item-2', 'entrega_id' => 'ent-1', 'contrato_id' => 'con-1'],
            ['id' => 'item-3', 'entrega_id' => 'ent-1', 'contrato_id' => 'con-x'],
        ]);

        $calRepo = $this->createMock(CalendarioRepositoryInterface::class);
        $calRepo->expects($this->exactly(2))->method('save')->willReturn('cal-1');

        $ventanaRepo = $this->createMock(VentanaEntregaRepositoryInterface::class);
        $ventanaRepo->expects($this->exactly(2))->method('save');

        $handler = new CalendarioEntregaCreadoHandler(
            $calRepo,
            $this->tx(),
            $ventanaRepo,
            new CalendarioItemRepository,
            new NullLogger
        );

        $payload = [
            'id' => 'cal-1',
            'fecha' => '2026-04-06',
            'hora' => '10:30:00',
            'entregaId' => 'ent-1',
            'contratoId' => 'con-1',
            'estado' => 1,
        ];

        $handler->handle($payload);
        $handler->handle($payload); // dedupe calendario_item insert path

        $this->assertSame(2, DB::table('calendario_item')->where('calendario_id', 'cal-1')->count());
    }

    public function test_calendario_entrega_creado_handler_hora_invalida_y_sin_entrega_id(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $calRepo = $this->createMock(CalendarioRepositoryInterface::class);
        $calRepo->expects($this->once())->method('save')->willReturn('cal-2');

        $ventanaRepo = $this->createMock(VentanaEntregaRepositoryInterface::class);
        $ventanaRepo->expects($this->never())->method('save');

        $handler = new CalendarioEntregaCreadoHandler(
            $calRepo,
            $this->tx(),
            $ventanaRepo,
            $this->makeNoOpCalendarioItemRepo(),
            $logger
        );

        $handler->handle([
            'id' => 'cal-2',
            'fecha' => '2026-04-06',
            'hora' => 'no-valida',
        ]);

        $this->assertSame(0, DB::table('calendario_item')->where('calendario_id', 'cal-2')->count());
    }

    public function test_calendario_entrega_creado_handler_private_build_ventana_id(): void
    {
        $handler = new CalendarioEntregaCreadoHandler(
            $this->createMock(CalendarioRepositoryInterface::class),
            $this->tx(),
            $this->createMock(VentanaEntregaRepositoryInterface::class),
            $this->makeNoOpCalendarioItemRepo()
        );

        $id = $this->invokePrivate($handler, 'buildVentanaId', ['ent-1', '2026-04-06', '10:00:00']);
        $this->assertMatchesRegularExpression('/^[0-9a-f\-]{36}$/', $id);
    }

    public function test_calendario_item_repository_link_items_sin_filtro_contrato(): void
    {
        DB::table('item_despacho')->insert([
            ['id' => 'item-10', 'entrega_id' => 'ent-10', 'contrato_id' => 'con-a'],
            ['id' => 'item-11', 'entrega_id' => 'ent-10', 'contrato_id' => null],
            ['id' => '', 'entrega_id' => 'ent-10', 'contrato_id' => 'con-b'],
        ]);

        $repo = new CalendarioItemRepository;
        $linked = $repo->linkItemsByEntregaId('ent-10', null, 'cal-10');

        $this->assertSame(2, $linked);
        $this->assertSame(2, DB::table('calendario_item')->where('calendario_id', 'cal-10')->count());
    }

    public function test_calendario_item_repository_link_items_deduplica(): void
    {
        DB::table('item_despacho')->insert([
            ['id' => 'item-20', 'entrega_id' => 'ent-20', 'contrato_id' => null],
        ]);

        $repo = new CalendarioItemRepository;
        $repo->linkItemsByEntregaId('ent-20', null, 'cal-20');
        $second = $repo->linkItemsByEntregaId('ent-20', null, 'cal-20');

        $this->assertSame(0, $second);
        $this->assertSame(1, DB::table('calendario_item')->where('calendario_id', 'cal-20')->count());
    }

    public function test_calendario_entrega_creado_handler_con_hora_valida_y_sin_entrega_id_usa_id_base(): void
    {
        $calRepo = $this->createMock(CalendarioRepositoryInterface::class);
        $calRepo->expects($this->once())->method('save')->willReturn('cal-20');

        $ventanaRepo = $this->createMock(VentanaEntregaRepositoryInterface::class);
        $ventanaRepo->expects($this->once())->method('save');

        $handler = new CalendarioEntregaCreadoHandler(
            $calRepo,
            $this->tx(),
            $ventanaRepo,
            $this->makeNoOpCalendarioItemRepo(),
            new NullLogger
        );
        $handler->handle([
            'id' => 'cal-20',
            'fecha' => '2026-04-06',
            'hora' => '07:15:00',
            'entregaId' => null,
        ]);

        $this->assertTrue(true);
    }

    public function test_calendario_generado_handler_procesa_fechas_validas_y_omite_invalidas(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $repo = $this->createMock(CalendarioRepositoryInterface::class);
        $repo->expects($this->exactly(2))->method('save');

        $handler = new CalendarioGeneradoHandler($repo, $this->tx(), $logger);
        $handler->handle([
            'contratoId' => 'con-1',
            'listaFechasEntrega' => ['2026-04-06', ' ', 'not-a-date', '2026-04-07T08:30:00+00:00'],
        ]);
    }

    public function test_calendario_generado_handler_ignora_si_lista_fechas_no_es_arreglo(): void
    {
        $repo = $this->createMock(CalendarioRepositoryInterface::class);
        $repo->expects($this->never())->method('save');

        $handler = new CalendarioGeneradoHandler($repo, $this->tx(), new NullLogger);
        $handler->handle(['listaFechasEntrega' => 'invalid']);

        $this->assertTrue(true);
    }

    public function test_calendario_servicio_generar_handler_loguea_info(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info');

        $handler = new CalendarioServicioGenerarHandler($logger);
        $handler->handle(['x' => 1], ['m' => 2]);
    }

    public function test_contrato_consultar_handler_loguea_info(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info');

        $handler = new ContratoConsultarHandler($logger);
        $handler->handle(['x' => 1], ['m' => 2]);
    }

    public function test_direccion_entrega_cambiada_handler_ignora_si_falta_paquete_id(): void
    {
        $repo = $this->createMock(PaqueteRepositoryInterface::class);
        $repo->expects($this->never())->method('save');

        $processManager = $this->createMock(CalendarProcessManager::class);
        $processManager->expects($this->once())->method('onDireccionEntregaCambiada');

        $handler = new DireccionEntregaCambiadaHandler($repo, $this->tx(), $processManager, new NullLogger);
        $handler->handle(['direccionId' => 'dir-1']);
    }

    public function test_direccion_entrega_cambiada_handler_ignora_si_paquete_no_existe(): void
    {
        $repo = $this->createMock(PaqueteRepositoryInterface::class);
        $repo->expects($this->once())->method('byId')->willThrowException(new EntityNotFoundException('missing'));
        $repo->expects($this->never())->method('save');

        $processManager = $this->createMock(CalendarProcessManager::class);
        $processManager->expects($this->once())->method('onDireccionEntregaCambiada');

        $handler = new DireccionEntregaCambiadaHandler($repo, $this->tx(), $processManager, new NullLogger);
        $handler->handle(['paqueteId' => 'paq-1', 'direccionId' => 'dir-1']);
    }

    public function test_direccion_entrega_cambiada_handler_actualiza_paquete(): void
    {
        $repo = $this->createMock(PaqueteRepositoryInterface::class);
        $repo->expects($this->once())->method('byId')->willReturn(new Paquete('paq-2', null, null, 'dir-old'));
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Paquete $paquete): bool => $paquete->direccionId === 'dir-new'));

        $processManager = $this->createMock(CalendarProcessManager::class);
        $processManager->expects($this->once())->method('onDireccionEntregaCambiada');

        $handler = new DireccionEntregaCambiadaHandler($repo, $this->tx(), $processManager, new NullLogger);
        $handler->handle(['paqueteId' => 'paq-2', 'direccionId' => 'dir-new']);
    }

    public function test_direccion_geocodificada_handler_ignora_si_falta_geo(): void
    {
        $repo = $this->createMock(DireccionRepositoryInterface::class);
        $repo->expects($this->never())->method('save');

        $handler = new DireccionGeocodificadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle(['id' => 'dir-1']);
    }

    public function test_direccion_geocodificada_handler_ignora_si_direccion_no_existe(): void
    {
        $repo = $this->createMock(DireccionRepositoryInterface::class);
        $repo->expects($this->once())->method('byId')->willThrowException(new EntityNotFoundException('missing'));
        $repo->expects($this->never())->method('save');

        $handler = new DireccionGeocodificadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle(['id' => 'dir-2', 'geo' => ['lat' => 4.1, 'lng' => -74.1]]);
    }

    public function test_direccion_geocodificada_handler_actualiza_geo(): void
    {
        $repo = $this->createMock(DireccionRepositoryInterface::class);
        $repo->expects($this->once())->method('byId')
            ->willReturn(new Direccion('dir-3', 'Casa', 'Calle 1'));
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Direccion $direccion): bool => is_array($direccion->geo)));

        $handler = new DireccionGeocodificadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle(['id' => 'dir-3', 'lat' => '4.6', 'lng' => '-74.09']);
    }

    public function test_suscripcion_crear_handler_usa_payload_id_y_fecha_fin_explicita(): void
    {
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(function (Suscripcion $suscripcion): bool {
                return $suscripcion->id === 'sus-1'
                    && $suscripcion->nombre === 'Plan A'
                    && $suscripcion->fechaFin === '2026-05-01';
            }));

        $handler = new SuscripcionCrearHandler($repo, $this->tx());
        $handler->handle([
            'id' => 'sus-1',
            'nombre' => 'Plan A',
            'tipoServicio' => 'mensual',
            'pacienteId' => 100,
            'fechaInicio' => '2026-04-01',
            'fechaFin' => '2026-05-01',
        ]);
    }

    public function test_suscripcion_crear_handler_usa_meta_aggregate_id_y_calcula_fecha_fin(): void
    {
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(function (Suscripcion $suscripcion): bool {
                return $suscripcion->id === 'agg-1'
                    && str_contains($suscripcion->nombre, 'premium #agg-1')
                    && $suscripcion->fechaFin === '2026-04-11';
            }));

        $handler = new SuscripcionCrearHandler($repo, $this->tx());
        $handler->handle([
            'tipoServicio' => 'premium',
            'fechaInicio' => '2026-04-01',
            'duracionDias' => 10,
        ], ['aggregate_id' => 'agg-1']);
    }

    public function test_suscripcion_crear_handler_usa_meta_correlation_id_si_no_hay_aggregate_id(): void
    {
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Suscripcion $suscripcion): bool => $suscripcion->id === 'corr-1'));

        $handler = new SuscripcionCrearHandler($repo, $this->tx());
        $handler->handle([
            'tipoServicio' => 'gold',
            'fechaInicio' => 'invalid-date',
            'duracionDias' => 'x',
        ], ['correlation_id' => 'corr-1']);
    }

    public function test_suscripcion_crear_handler_genera_uuid_si_no_hay_ids(): void
    {
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Suscripcion $suscripcion): bool => is_string($suscripcion->id) && strlen($suscripcion->id) === 36));

        $handler = new SuscripcionCrearHandler($repo, $this->tx());
        $handler->handle(['tipoServicio' => 'lite']);
    }

    public function test_suscripcion_crear_handler_metodos_privados_resuelven_campos(): void
    {
        $handler = new SuscripcionCrearHandler(
            $this->createMock(SuscripcionRepositoryInterface::class),
            $this->tx()
        );

        $id = $this->invokePrivate($handler, 'resolveId', [
            ['id' => null, 'suscripcionId' => 'sus-20'],
            [],
        ]);
        $this->assertSame('sus-20', $id);

        $fechaFin = $this->invokePrivate($handler, 'resolveFechaFin', [
            ['duracionDias' => 5],
            '2026-04-01',
        ]);
        $this->assertSame('2026-04-06', $fechaFin);

        $stringA = $this->invokePrivate($handler, 'getString', [['valor' => 12.5], 'valor']);
        $stringB = $this->invokePrivate($handler, 'getString', [['valor' => ['x' => 1]], 'valor']);
        $stringC = $this->invokePrivate($handler, 'getString', [['valor' => ''], 'valor']);
        $fechaInvalida = $this->invokePrivate($handler, 'resolveFechaFin', [
            ['duracionDias' => 2],
            'fecha-invalida',
        ]);
        $this->assertSame('12.5', $stringA);
        $this->assertNull($stringB);
        $this->assertNull($stringC);
        $this->assertNull($fechaInvalida);
    }

    private function makeNoOpCalendarioItemRepo(): CalendarioItemRepositoryInterface
    {
        return new class implements CalendarioItemRepositoryInterface
        {
            public function byId(string|int $id): ?\App\Domain\Produccion\Entity\CalendarioItem
            {
                return null;
            }

            public function save(\App\Domain\Produccion\Entity\CalendarioItem $item): string
            {
                return '';
            }

            public function list(): array
            {
                return [];
            }

            public function delete(string|int $id): void
            {
                // intentionally empty — test stub
            }

            public function deleteByCalendarioId(string|int $calendarioId): void
            {
                // intentionally empty — test stub
            }

            public function linkItemsByEntregaId(string $entregaId, ?string $contratoId, string $calendarioId): int
            {
                return 0;
            }
        };
    }

    private function tx(): TransactionAggregate
    {
        $manager = new class implements TransactionManagerInterface
        {
            public function run(callable $callback): mixed
            {
                return $callback();
            }

            public function afterCommit(callable $callback): void {}
        };

        return new TransactionAggregate($manager);
    }

    private function invokePrivate(object $target, string $methodName, array $arguments = []): mixed
    {
        $reflection = new ReflectionClass($target);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($target, $arguments);
    }

    private function createSchema(): void
    {
        Schema::create('item_despacho', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('entrega_id')->nullable();
            $table->string('contrato_id')->nullable();
        });

        Schema::create('calendario_item', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('calendario_id')->nullable();
            $table->string('item_despacho_id')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }
}

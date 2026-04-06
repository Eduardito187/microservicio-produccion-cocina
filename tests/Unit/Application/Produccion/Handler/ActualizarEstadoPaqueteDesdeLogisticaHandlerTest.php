<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion\Handler;

use App\Application\Analytics\KpiRepositoryInterface;
use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Application\Produccion\Command\ActualizarEstadoPaqueteDesdeLogisticaCommand;
use App\Application\Produccion\Handler\ActualizarEstadoPaqueteDesdeLogisticaHandler;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Psr\Log\NullLogger;
use ReflectionClass;
use Tests\TestCase;

/**
 * @class ActualizarEstadoPaqueteDesdeLogisticaHandlerTest
 */
class ActualizarEstadoPaqueteDesdeLogisticaHandlerTest extends TestCase
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

    public function test_inserta_historial_evidencia_y_encola_inconsistencia_si_paquete_no_existe(): void
    {
        $kpiSpy = new class implements KpiRepositoryInterface
        {
            /** @var array<int, array{name:string,by:int}> */
            public array $calls = [];

            public function increment(string $name, int $by = 1): void
            {
                $this->calls[] = ['name' => $name, 'by' => $by];
            }
        };

        $evidenciaSpy = new class implements EntregaEvidenciaRepositoryInterface
        {
            /** @var array<int, array{eventId:string,data:array}> */
            public array $calls = [];

            public function upsertByEventId(string $eventId, array $data): void
            {
                $this->calls[] = ['eventId' => $eventId, 'data' => $data];
            }
        };

        $publisherSpy = new class implements DomainEventPublisherInterface
        {
            /** @var array<int, array{events:array,aggregateId:mixed}> */
            public array $calls = [];

            public function publish(array $events, mixed $aggregateId): void
            {
                $this->calls[] = ['events' => $events, 'aggregateId' => $aggregateId];
            }
        };

        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            $evidenciaSpy,
            $kpiSpy,
            $this->tx(),
            $publisherSpy,
            new NullLogger
        );

        $command = new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-log-1',
            'pkg-404',
            'delivered',
            '2026-04-06T10:00:00+00:00',
            '123e4567-e89b-12d3-a456-426614174000',
            ['url' => 'https://example.test/foto.jpg', 'geo' => ['lat' => 1.23, 'lng' => -77.5]],
            ['source' => 'logistica']
        );

        $handler($command);

        $history = DB::table('package_delivery_history')->where('event_id', 'evt-log-1')->first();
        $this->assertNotNull($history);
        $this->assertSame('pkg-404', $history->package_id);
        $this->assertSame('delivered', $history->received_status);

        $this->assertCount(1, $evidenciaSpy->calls);
        $this->assertSame('evt-log-1', $evidenciaSpy->calls[0]['eventId']);
        $this->assertSame('confirmada', $evidenciaSpy->calls[0]['data']['status']);

        $inconsistency = DB::table('delivery_inconsistency_queue')
            ->where('event_id', 'evt-log-1')
            ->where('reason', 'package_without_dispatch_relation')
            ->first();
        $this->assertNotNull($inconsistency);

        $metricNames = array_map(static fn (array $entry): string => $entry['name'], $kpiSpy->calls);
        $this->assertContains('delivery_events_processed_total', $metricNames);
        $this->assertContains('entrega_confirmada', $metricNames);
        $this->assertContains('alert_package_unknown', $metricNames);
        $this->assertContains('delivery_inconsistency_events', $metricNames);

        $this->assertCount(1, $publisherSpy->calls);
    }

    public function test_reprocesar_mismo_evento_actualiza_sin_duplicar_inconsistencia(): void
    {
        $kpiSpy = new class implements KpiRepositoryInterface
        {
            /** @var array<int, string> */
            public array $names = [];

            public function increment(string $name, int $by = 1): void
            {
                $this->names[] = $name;
            }
        };

        $evidenciaSpy = new class implements EntregaEvidenciaRepositoryInterface
        {
            public function upsertByEventId(string $eventId, array $data): void {}
        };

        $publisherSpy = new class implements DomainEventPublisherInterface
        {
            public int $published = 0;

            public function publish(array $events, mixed $aggregateId): void
            {
                $this->published++;
            }
        };

        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            $evidenciaSpy,
            $kpiSpy,
            $this->tx(),
            $publisherSpy,
            new NullLogger
        );

        $command = new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-log-2',
            'pkg-404',
            'failed',
            null,
            null,
            'foto-inline',
            ['attempt' => 1]
        );

        $handler($command);
        $handler($command);

        $this->assertSame(1, DB::table('delivery_inconsistency_queue')->where('event_id', 'evt-log-2')->count());
        $this->assertSame(1, $publisherSpy->published);
        $this->assertSame(1, DB::table('package_delivery_history')->where('event_id', 'evt-log-2')->count());
        $this->assertContains('entrega_fallida', $kpiSpy->names);
    }

    public function test_cuando_todos_los_paquetes_de_la_op_quedan_confirmados_publica_evento_consolidado(): void
    {
        DB::table('item_despacho')->insert([
            'id' => 'item-1',
            'paquete_id' => 'pkg-1',
            'op_id' => 'op-1',
            'delivery_status' => 'en_ruta',
            'entrega_id' => null,
            'contrato_id' => null,
            'ventana_entrega_id' => 'ven-1',
            'driver_id' => null,
            'delivery_occurred_on' => null,
            'updated_at' => now(),
        ]);

        DB::table('ventana_entrega')->insert([
            'id' => 'ven-1',
            'entrega_id' => '123e4567-e89b-12d3-a456-426614174010',
            'contrato_id' => '123e4567-e89b-12d3-a456-426614174011',
        ]);

        DB::table('calendario_item')->insert([
            'id' => 'ci-1',
            'item_despacho_id' => 'item-1',
            'calendario_id' => 'cal-1',
        ]);

        DB::table('orden_produccion')->insert([
            'id' => 'op-1',
            'entrega_completada_at' => null,
            'updated_at' => now(),
        ]);

        $kpiSpy = new class implements KpiRepositoryInterface
        {
            /** @var array<int, string> */
            public array $names = [];

            public function increment(string $name, int $by = 1): void
            {
                $this->names[] = $name;
            }
        };

        $evidenciaSpy = new class implements EntregaEvidenciaRepositoryInterface
        {
            public function upsertByEventId(string $eventId, array $data): void {}
        };

        $publisherSpy = new class implements DomainEventPublisherInterface
        {
            public int $published = 0;

            public function publish(array $events, mixed $aggregateId): void
            {
                $this->published += count($events);
            }
        };

        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            $evidenciaSpy,
            $kpiSpy,
            $this->tx(),
            $publisherSpy,
            new NullLogger
        );

        $handler(new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-log-3',
            'pkg-1',
            'entregado',
            '2026-04-06T11:00:00+00:00',
            '123e4567-e89b-12d3-a456-426614174000',
            ['fotoUrl' => 'https://example.test/ok.jpg', 'geolocation' => ['lat' => 4.7, 'lng' => -74.1]],
            ['source' => 'logistica']
        ));

        $this->assertSame('confirmada', DB::table('item_despacho')->where('id', 'item-1')->value('delivery_status'));
        $this->assertSame(1, DB::table('package_delivery_tracking')->where('package_id', 'pkg-1')->count());
        $this->assertSame(1, DB::table('order_delivery_progress')->where('op_id', 'op-1')->count());
        $this->assertNotNull(DB::table('order_delivery_progress')->where('op_id', 'op-1')->value('completion_event_id'));
        $this->assertNotNull(DB::table('orden_produccion')->where('id', 'op-1')->value('entrega_completada_at'));
        $this->assertSame(1, $publisherSpy->published);
        $this->assertContains('delivery_packages_completed', $kpiSpy->names);
        $this->assertContains('delivery_orders_completed', $kpiSpy->names);
    }

    public function test_transicion_desde_estado_terminal_no_cambia_el_estado_y_registra_kpi_de_bloqueo(): void
    {
        DB::table('item_despacho')->insert([
            'id' => 'item-2',
            'paquete_id' => 'pkg-2',
            'op_id' => 'op-2',
            'delivery_status' => 'confirmada',
            'entrega_id' => '123e4567-e89b-12d3-a456-426614174010',
            'contrato_id' => '123e4567-e89b-12d3-a456-426614174011',
            'ventana_entrega_id' => null,
            'driver_id' => null,
            'delivery_occurred_on' => null,
            'updated_at' => now(),
        ]);

        $kpiSpy = new class implements KpiRepositoryInterface
        {
            /** @var array<int, string> */
            public array $names = [];

            public function increment(string $name, int $by = 1): void
            {
                $this->names[] = $name;
            }
        };

        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            new class implements EntregaEvidenciaRepositoryInterface
            {
                public function upsertByEventId(string $eventId, array $data): void {}
            },
            $kpiSpy,
            $this->tx(),
            new class implements DomainEventPublisherInterface
            {
                public function publish(array $events, mixed $aggregateId): void {}
            },
            new NullLogger
        );

        $handler(new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-log-4',
            'pkg-2',
            'fallida',
            null,
            null,
            null,
            ['source' => 'logistica']
        ));

        $this->assertSame('confirmada', DB::table('item_despacho')->where('id', 'item-2')->value('delivery_status'));
        $this->assertContains('delivery_state_blocked_terminal', $kpiSpy->names);
    }

    public function test_metodos_privados_de_parseo_y_mapeo_cubren_ramas_principales(): void
    {
        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            new class implements EntregaEvidenciaRepositoryInterface
            {
                public function upsertByEventId(string $eventId, array $data): void {}
            },
            new class implements KpiRepositoryInterface
            {
                public function increment(string $name, int $by = 1): void {}
            },
            $this->tx(),
            new class implements DomainEventPublisherInterface
            {
                public function publish(array $events, mixed $aggregateId): void {}
            },
            new NullLogger
        );

        [$confirmada, $kpiConfirmada] = $this->invokePrivate($handler, 'mapStatus', ['delivered']);
        [$fallida, $kpiFallida] = $this->invokePrivate($handler, 'mapStatus', ['fallida']);
        [$enRuta, $kpiEnRuta] = $this->invokePrivate($handler, 'mapStatus', ['intransit']);
        [$default, $kpiDefault] = $this->invokePrivate($handler, 'mapStatus', ['desconocido']);

        $this->assertSame('confirmada', $confirmada->value());
        $this->assertSame('entrega_confirmada', $kpiConfirmada);
        $this->assertSame('fallida', $fallida->value());
        $this->assertSame('entrega_fallida', $kpiFallida);
        $this->assertSame('en_ruta', $enRuta->value());
        $this->assertSame('paquete_en_ruta', $kpiEnRuta);
        $this->assertSame('estado_actualizado', $default->value());
        $this->assertNull($kpiDefault);

        $this->assertNull($this->invokePrivate($handler, 'parseOccurredOn', [null]));
        $this->assertNull($this->invokePrivate($handler, 'parseOccurredOn', ['  ']));
        $this->assertNotNull($this->invokePrivate($handler, 'parseOccurredOn', ['2026-04-06T10:00:00+00:00']));
        $this->assertNull($this->invokePrivate($handler, 'parseOccurredOn', ['not-a-date']));

        $this->assertNull($this->invokePrivate($handler, 'parseDriverId', [null]));
        $this->assertNotNull($this->invokePrivate($handler, 'parseDriverId', ['123e4567-e89b-12d3-a456-426614174000']));
        $this->assertNull($this->invokePrivate($handler, 'parseDriverId', ['driver-invalid']));

        $this->assertNull($this->invokePrivate($handler, 'parseStoredStatus', [null]));
        $this->assertNotNull($this->invokePrivate($handler, 'parseStoredStatus', ['confirmada']));
        $this->assertNull($this->invokePrivate($handler, 'parseStoredStatus', ['estado_invalido']));

        $this->assertTrue($this->invokePrivate($handler, 'isUuid', ['123e4567-e89b-12d3-a456-426614174000']));
        $this->assertFalse($this->invokePrivate($handler, 'isUuid', ['']));
        $this->assertFalse($this->invokePrivate($handler, 'isUuid', ['not-uuid']));
    }

    public function test_metodos_privados_de_persistencia_y_progreso_quedan_cubiertos(): void
    {
        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            new class implements EntregaEvidenciaRepositoryInterface
            {
                public function upsertByEventId(string $eventId, array $data): void {}
            },
            new class implements KpiRepositoryInterface
            {
                public function increment(string $name, int $by = 1): void {}
            },
            $this->tx(),
            new class implements DomainEventPublisherInterface
            {
                public function publish(array $events, mixed $aggregateId): void {}
            },
            new NullLogger
        );

        DB::table('item_despacho')->insert([
            'id' => 'item-p1',
            'paquete_id' => 'pkg-p1',
            'op_id' => 'op-p1',
            'delivery_status' => 'en_ruta',
            'entrega_id' => null,
            'contrato_id' => null,
            'ventana_entrega_id' => 'ven-p1',
            'driver_id' => null,
            'delivery_occurred_on' => null,
            'updated_at' => now(),
        ]);

        DB::table('ventana_entrega')->insert([
            'id' => 'ven-p1',
            'entrega_id' => '123e4567-e89b-12d3-a456-426614174050',
            'contrato_id' => '123e4567-e89b-12d3-a456-426614174051',
        ]);

        DB::table('calendario_item')->insert([
            'id' => 'ci-p1',
            'item_despacho_id' => 'item-p1',
            'calendario_id' => 'cal-p1',
        ]);

        $this->invokePrivate($handler, 'persistHistory', [
            'evt-priv-1',
            'pkg-p1',
            'delivered',
            null,
            ['url' => 'https://example.test/foto.jpg'],
            ['source' => 'test'],
            null,
        ]);

        // Second call covers update branch in persistHistory.
        $this->invokePrivate($handler, 'persistHistory', [
            'evt-priv-1',
            'pkg-p1',
            'failed',
            null,
            'inline-evidence',
            ['source' => 'test2'],
            null,
        ]);

        $this->invokePrivate($handler, 'backfillDeliveryContextForPackage', ['pkg-p1']);
        $this->assertNotNull(DB::table('item_despacho')->where('id', 'item-p1')->value('entrega_id'));

        DB::table('item_despacho')->insert([
            'id' => 'item-p2',
            'paquete_id' => 'pkg-p1',
            'op_id' => null,
            'delivery_status' => null,
            'entrega_id' => null,
            'contrato_id' => null,
            'ventana_entrega_id' => 'ven-missing',
            'driver_id' => null,
            'delivery_occurred_on' => null,
            'updated_at' => now(),
        ]);

        // Covers branch when ventana_entrega_id exists but ventana is missing.
        $this->invokePrivate($handler, 'backfillDeliveryContextForPackage', ['pkg-p1']);

        $this->invokePrivate($handler, 'enqueueInconsistency', [
            'op-p1',
            'evt-priv-2',
            'pkg-p1',
            'manual_reason',
            ['a' => 1],
        ]);

        // Second call with same keys covers dedupe update path.
        $this->invokePrivate($handler, 'enqueueInconsistency', [
            'op-p1',
            'evt-priv-2',
            'pkg-p1',
            'manual_reason',
            ['a' => 2],
        ]);

        $this->invokePrivate($handler, 'upsertTracking', [
            'pkg-p1',
            'op-p1',
            '123e4567-e89b-12d3-a456-426614174050',
            '123e4567-e89b-12d3-a456-426614174051',
            null,
            'en_ruta',
            false,
            null,
            'evt-priv-3',
            null,
        ]);

        DB::table('package_delivery_tracking')->where('package_id', 'pkg-p1')->update([
            'completed_at' => '2026-04-06 12:00:00',
        ]);
        // Covers completedAt hydration from existing tracking row.
        $this->invokePrivate($handler, 'upsertTracking', [
            'pkg-p1',
            'op-p1',
            '123e4567-e89b-12d3-a456-426614174050',
            '123e4567-e89b-12d3-a456-426614174051',
            null,
            'confirmada',
            true,
            null,
            'evt-priv-3b',
            null,
        ]);

        DB::table('item_despacho')->insert([
            'id' => 'item-p3',
            'paquete_id' => 'pkg-p3',
            'op_id' => 'op-p2',
            'delivery_status' => 'confirmada',
            'entrega_id' => 'invalid-entrega',
            'contrato_id' => 'invalid-contrato',
            'ventana_entrega_id' => null,
            'driver_id' => null,
            'delivery_occurred_on' => null,
            'updated_at' => now(),
        ]);

        DB::table('order_delivery_progress')->insert([
            'id' => 'odp-p2',
            'op_id' => 'op-p2',
            'total_packages' => 1,
            'completed_packages' => 1,
            'pending_packages' => 0,
            'all_completed_at' => '2026-04-06 11:00:00',
            'entrega_id' => null,
            'contrato_id' => null,
            'completion_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Covers existing all_completed_at branch plus invalid EntregaId/ContratoId catch branches.
        $projectionInvalid = $this->invokePrivate($handler, 'syncOrderProgress', ['op-p2', null]);
        $this->assertNull($projectionInvalid['entrega_id']);
        $this->assertNull($projectionInvalid['contrato_id']);

        $projection = $this->invokePrivate($handler, 'syncOrderProgress', ['op-p1', null]);
        $this->assertSame(1, $projection['total_packages']);
        $this->assertSame(0, $projection['completed_packages']);
        $this->assertSame('cal-p1', $projection['calendario_id']);
    }

    public function test_invoke_cubre_normalizacion_de_evidencia_invalida(): void
    {
        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            new class implements EntregaEvidenciaRepositoryInterface
            {
                public function upsertByEventId(string $eventId, array $data): void {}
            },
            new class implements KpiRepositoryInterface
            {
                public function increment(string $name, int $by = 1): void {}
            },
            $this->tx(),
            new class implements DomainEventPublisherInterface
            {
                public function publish(array $events, mixed $aggregateId): void {}
            },
            new NullLogger
        );

        $handler(new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-norm-1',
            'pkg-norm-1',
            'delivered',
            null,
            null,
            ['url' => 123, 'geo' => 'not-array'],
            ['source' => 'coverage']
        ));

        $this->assertSame(1, DB::table('package_delivery_history')->where('event_id', 'evt-norm-1')->count());
    }

    public function test_missing_op_id_en_item_despacho_dispara_alerta_y_inconsistencia(): void
    {
        DB::table('item_despacho')->insert([
            'id' => 'item-missing-op',
            'paquete_id' => 'pkg-missing-op',
            'op_id' => null,
            'delivery_status' => null,
            'entrega_id' => null,
            'contrato_id' => null,
            'ventana_entrega_id' => null,
            'driver_id' => null,
            'delivery_occurred_on' => null,
            'updated_at' => now(),
        ]);

        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            new class implements EntregaEvidenciaRepositoryInterface
            {
                public function upsertByEventId(string $eventId, array $data): void {}
            },
            new class implements KpiRepositoryInterface
            {
                public array $names = [];

                public function increment(string $name, int $by = 1): void
                {
                    $this->names[] = $name;
                }
            },
            $this->tx(),
            new class implements DomainEventPublisherInterface
            {
                public function publish(array $events, mixed $aggregateId): void {}
            },
            new NullLogger
        );

        $handler(new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-missing-op',
            'pkg-missing-op',
            'en_ruta',
            null,
            null,
            null,
            ['source' => 'coverage']
        ));

        $this->assertSame(1, DB::table('delivery_inconsistency_queue')->where('reason', 'missing_op_id_for_package')->count());
    }

    public function test_consolidacion_sin_contexto_dispara_alerta_missing_delivery_context(): void
    {
        DB::table('item_despacho')->insert([
            'id' => 'item-no-context',
            'paquete_id' => 'pkg-no-context',
            'op_id' => 'op-no-context',
            'delivery_status' => 'en_ruta',
            'entrega_id' => null,
            'contrato_id' => null,
            'ventana_entrega_id' => null,
            'driver_id' => null,
            'delivery_occurred_on' => null,
            'updated_at' => now(),
        ]);

        $kpi = new class implements KpiRepositoryInterface
        {
            public array $names = [];

            public function increment(string $name, int $by = 1): void
            {
                $this->names[] = $name;
            }
        };

        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            new class implements EntregaEvidenciaRepositoryInterface
            {
                public function upsertByEventId(string $eventId, array $data): void {}
            },
            $kpi,
            $this->tx(),
            new class implements DomainEventPublisherInterface
            {
                public function publish(array $events, mixed $aggregateId): void {}
            },
            new NullLogger
        );

        $handler(new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-no-context',
            'pkg-no-context',
            'failed',
            null,
            null,
            null,
            ['source' => 'coverage']
        ));

        $this->assertContains('alert_missing_delivery_context', $kpi->names);
        $this->assertSame(1, DB::table('delivery_inconsistency_queue')->where('reason', 'missing_delivery_context_for_consolidated_event')->count());
    }

    public function test_consolidacion_con_completion_event_ya_existente_cubre_marked_progress_cero(): void
    {
        DB::table('item_despacho')->insert([
            'id' => 'item-marked-zero',
            'paquete_id' => 'pkg-marked-zero',
            'op_id' => 'op-marked-zero',
            'delivery_status' => 'en_ruta',
            'entrega_id' => '123e4567-e89b-12d3-a456-426614174070',
            'contrato_id' => '123e4567-e89b-12d3-a456-426614174071',
            'ventana_entrega_id' => null,
            'driver_id' => null,
            'delivery_occurred_on' => null,
            'updated_at' => now(),
        ]);

        DB::table('calendario_item')->insert([
            'id' => 'ci-marked-zero',
            'item_despacho_id' => 'item-marked-zero',
            'calendario_id' => 'cal-marked-zero',
        ]);

        DB::table('order_delivery_progress')->insert([
            'id' => 'odp-marked-zero',
            'op_id' => 'op-marked-zero',
            'total_packages' => 1,
            'completed_packages' => 0,
            'pending_packages' => 1,
            'all_completed_at' => null,
            'entrega_id' => '123e4567-e89b-12d3-a456-426614174070',
            'contrato_id' => '123e4567-e89b-12d3-a456-426614174071',
            'completion_event_id' => 'already-set',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            new class implements EntregaEvidenciaRepositoryInterface
            {
                public function upsertByEventId(string $eventId, array $data): void {}
            },
            new class implements KpiRepositoryInterface
            {
                public function increment(string $name, int $by = 1): void {}
            },
            $this->tx(),
            new class implements DomainEventPublisherInterface
            {
                public function publish(array $events, mixed $aggregateId): void {}
            },
            new NullLogger
        );

        $handler(new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-marked-zero',
            'pkg-marked-zero',
            'delivered',
            null,
            null,
            null,
            ['source' => 'coverage']
        ));

        $this->assertSame('already-set', DB::table('order_delivery_progress')->where('op_id', 'op-marked-zero')->value('completion_event_id'));
    }

    public function test_consolidacion_parcial_cubre_branch_all_delivered_false_all_failed_false(): void
    {
        DB::table('item_despacho')->insert([
            [
                'id' => 'item-partial-a',
                'paquete_id' => 'pkg-partial-a',
                'op_id' => 'op-partial',
                'delivery_status' => 'en_ruta',
                'entrega_id' => '123e4567-e89b-12d3-a456-426614174080',
                'contrato_id' => '123e4567-e89b-12d3-a456-426614174081',
                'ventana_entrega_id' => null,
                'driver_id' => null,
                'delivery_occurred_on' => null,
                'updated_at' => now(),
            ],
            [
                'id' => 'item-partial-b',
                'paquete_id' => 'pkg-partial-b',
                'op_id' => 'op-partial',
                'delivery_status' => 'en_ruta',
                'entrega_id' => '123e4567-e89b-12d3-a456-426614174080',
                'contrato_id' => '123e4567-e89b-12d3-a456-426614174081',
                'ventana_entrega_id' => null,
                'driver_id' => null,
                'delivery_occurred_on' => null,
                'updated_at' => now(),
            ],
        ]);

        DB::table('calendario_item')->insert([
            [
                'id' => 'ci-partial-a',
                'item_despacho_id' => 'item-partial-a',
                'calendario_id' => 'cal-partial',
            ],
            [
                'id' => 'ci-partial-b',
                'item_despacho_id' => 'item-partial-b',
                'calendario_id' => 'cal-partial',
            ],
        ]);

        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            new class implements EntregaEvidenciaRepositoryInterface
            {
                public function upsertByEventId(string $eventId, array $data): void {}
            },
            new class implements KpiRepositoryInterface
            {
                public function increment(string $name, int $by = 1): void {}
            },
            $this->tx(),
            new class implements DomainEventPublisherInterface
            {
                public int $published = 0;

                public function publish(array $events, mixed $aggregateId): void
                {
                    $this->published += count($events);
                }
            },
            new NullLogger
        );

        $handler(new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-partial',
            'pkg-partial-a',
            'delivered',
            null,
            null,
            null,
            ['source' => 'coverage']
        ));

        $this->assertSame(0, DB::table('order_delivery_progress')->where('op_id', 'op-partial')->whereNotNull('completion_event_id')->count());
    }

    public function test_projection_total_packages_cero_cubre_continue_defensivo(): void
    {
        DB::table('item_despacho')->insert([
            'id' => 'item-zero-total',
            'paquete_id' => 'pkg-zero-total',
            'op_id' => 'op-zero-total',
            'delivery_status' => null,
            'entrega_id' => null,
            'contrato_id' => null,
            'ventana_entrega_id' => null,
            'driver_id' => null,
            'delivery_occurred_on' => null,
            'updated_at' => now(),
        ]);

        // Force defensive branch: after the row update inside handler, package_id becomes null,
        // so syncOrderProgress sees total_packages = 0 for that op.
        DB::unprepared('CREATE TRIGGER trg_zero_total_packages AFTER UPDATE ON item_despacho BEGIN UPDATE item_despacho SET paquete_id = NULL WHERE op_id = NEW.op_id; END;');

        $handler = new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            new class implements EntregaEvidenciaRepositoryInterface
            {
                public function upsertByEventId(string $eventId, array $data): void {}
            },
            new class implements KpiRepositoryInterface
            {
                public function increment(string $name, int $by = 1): void {}
            },
            $this->tx(),
            new class implements DomainEventPublisherInterface
            {
                public function publish(array $events, mixed $aggregateId): void {}
            },
            new NullLogger
        );

        $handler(new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-zero-total',
            'pkg-zero-total',
            'en_ruta',
            null,
            null,
            null,
            ['source' => 'coverage']
        ));

        $this->assertSame(0, (int) DB::table('item_despacho')->where('op_id', 'op-zero-total')->whereNotNull('paquete_id')->count());
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
        Schema::create('package_delivery_history', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('event_id')->unique();
            $table->string('package_id');
            $table->string('received_status')->nullable();
            $table->string('driver_id')->nullable();
            $table->text('evidence')->nullable();
            $table->text('payload')->nullable();
            $table->dateTime('occurred_on')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('item_despacho', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('paquete_id')->nullable();
            $table->string('op_id')->nullable();
            $table->string('delivery_status')->nullable();
            $table->string('entrega_id')->nullable();
            $table->string('contrato_id')->nullable();
            $table->string('ventana_entrega_id')->nullable();
            $table->string('driver_id')->nullable();
            $table->dateTime('delivery_occurred_on')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('delivery_inconsistency_queue', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('event_id')->nullable();
            $table->string('package_id')->nullable();
            $table->string('op_id')->nullable();
            $table->string('reason');
            $table->text('payload')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('package_delivery_tracking', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('package_id')->unique();
            $table->string('op_id')->nullable();
            $table->string('entrega_id')->nullable();
            $table->string('contrato_id')->nullable();
            $table->string('driver_id')->nullable();
            $table->string('status')->nullable();
            $table->boolean('status_locked')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->string('last_event_id')->nullable();
            $table->dateTime('last_occurred_on')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('order_delivery_progress', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('op_id')->unique();
            $table->integer('total_packages')->default(0);
            $table->integer('completed_packages')->default(0);
            $table->integer('pending_packages')->default(0);
            $table->dateTime('all_completed_at')->nullable();
            $table->string('entrega_id')->nullable();
            $table->string('contrato_id')->nullable();
            $table->string('completion_event_id')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('orden_produccion', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->dateTime('entrega_completada_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('calendario_item', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('item_despacho_id')->nullable();
            $table->string('calendario_id')->nullable();
        });

        Schema::create('ventana_entrega', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('entrega_id')->nullable();
            $table->string('contrato_id')->nullable();
        });
    }
}

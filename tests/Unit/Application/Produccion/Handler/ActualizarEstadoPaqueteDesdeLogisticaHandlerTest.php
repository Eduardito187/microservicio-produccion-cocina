<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion\Handler;

use App\Application\Analytics\KpiRepositoryInterface;
use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Application\Produccion\Command\ActualizarEstadoPaqueteDesdeLogisticaCommand;
use App\Application\Produccion\Handler\ActualizarEstadoPaqueteDesdeLogisticaHandler;
use App\Application\Produccion\Repository\DeliveryInconsistencyQueueRepositoryInterface;
use App\Application\Produccion\Repository\OrderDeliveryProgressRepositoryInterface;
use App\Application\Produccion\Repository\PackageDeliveryHistoryRepositoryInterface;
use App\Application\Produccion\Repository\PackageDeliveryTrackingRepositoryInterface;
use App\Application\Produccion\Service\DeliveryContextBackfiller;
use App\Application\Produccion\Service\DeliveryStatusMapper;
use App\Application\Produccion\Service\OrderDeliveryProgressSync;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use DateTimeImmutable;
use Psr\Log\NullLogger;
use Tests\TestCase;

/**
 * @class ActualizarEstadoPaqueteDesdeLogisticaHandlerTest
 */
class ActualizarEstadoPaqueteDesdeLogisticaHandlerTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    public function test_encola_inconsistencia_si_paquete_no_tiene_items_despacho(): void
    {
        $kpi = $this->makeKpiSpy();
        $inconsistency = $this->makeInconsistencyRepo();
        $history = $this->makeHistoryRepo();

        $handler = $this->buildHandler([
            'kpi' => $kpi,
            'itemRepo' => $this->makeItemRepoWithRows([]),
            'inconsistencyRepo' => $inconsistency,
            'historyRepo' => $history,
        ]);

        $handler($this->makeCommand('evt-1', 'pkg-404', 'delivered'));

        $this->assertContains('alert_package_unknown', $kpi->names);
        $this->assertContains('delivery_inconsistency_events', $kpi->names);
        $this->assertSame(1, $inconsistency->insertCount);
        $this->assertSame(1, $history->insertCount);
    }

    public function test_no_duplica_inconsistencia_al_reprocesar_mismo_evento(): void
    {
        $publisher = $this->makePublisherSpy();
        $inconsistency = $this->makeInconsistencyRepo();
        $history = $this->makeHistoryRepo();

        $handler = $this->buildHandler([
            'itemRepo' => $this->makeItemRepoWithRows([]),
            'publisher' => $publisher,
            'inconsistencyRepo' => $inconsistency,
            'historyRepo' => $history,
        ]);

        $cmd = $this->makeCommand('evt-2', 'pkg-404', 'failed');
        $handler($cmd);
        $handler($cmd);

        $this->assertSame(1, $publisher->published);
        $this->assertSame(1, $inconsistency->insertCount);
        $this->assertSame(1, $history->insertCount);
        $this->assertSame(1, $history->updateCount);
    }

    public function test_actualiza_item_despacho_y_upsert_tracking_cuando_hay_rows(): void
    {
        $kpi = $this->makeKpiSpy();
        $itemRepo = $this->makeItemRepoWithRows([
            (object) ['id' => 'item-1', 'op_id' => 'op-1', 'delivery_status' => 'en_ruta', 'entrega_id' => null, 'contrato_id' => null, 'ventana_entrega_id' => null],
        ]);
        $tracking = $this->makeTrackingRepo();

        $handler = $this->buildHandler([
            'kpi' => $kpi,
            'itemRepo' => $itemRepo,
            'trackingRepo' => $tracking,
        ]);

        $handler($this->makeCommand('evt-3', 'pkg-1', 'delivered'));

        $this->assertSame(1, count($itemRepo->updatedFields));
        $this->assertSame(1, $tracking->upsertCount);
        $this->assertContains('delivery_packages_completed', $kpi->names);
    }

    public function test_bloquea_transicion_desde_estado_terminal(): void
    {
        $kpi = $this->makeKpiSpy();
        $itemRepo = $this->makeItemRepoWithRows([
            (object) ['id' => 'item-2', 'op_id' => 'op-2', 'delivery_status' => 'confirmada', 'entrega_id' => '123e4567-e89b-12d3-a456-426614174010', 'contrato_id' => '123e4567-e89b-12d3-a456-426614174011', 'ventana_entrega_id' => null],
        ]);

        $handler = $this->buildHandler(['kpi' => $kpi, 'itemRepo' => $itemRepo]);

        $handler($this->makeCommand('evt-4', 'pkg-2', 'fallida'));

        $this->assertContains('delivery_state_blocked_terminal', $kpi->names);
    }

    public function test_publica_evento_consolidado_cuando_todos_los_paquetes_confirmados(): void
    {
        $publisher = $this->makePublisherSpy();
        $kpi = $this->makeKpiSpy();

        $itemRepo = $this->makeItemRepoWithRows([
            (object) ['id' => 'item-3', 'op_id' => 'op-3', 'delivery_status' => 'en_ruta', 'entrega_id' => null, 'contrato_id' => null, 'ventana_entrega_id' => null],
        ]);

        $handler = $this->buildHandler([
            'kpi' => $kpi,
            'itemRepo' => $itemRepo,
            'publisher' => $publisher,
            'progressRepo' => $this->makeProgressRepoMarkingRows(1),
            'progressSync' => $this->makeProgressSyncReturning([
                'total_packages' => 1, 'completed_packages' => 1, 'failed_packages' => 0,
                'pending_packages' => 0, 'all_completed_at' => '2026-04-06 12:00:00',
                'entrega_id' => '123e4567-e89b-12d3-a456-426614174010',
                'contrato_id' => '123e4567-e89b-12d3-a456-426614174011',
                'calendario_id' => 'cal-1',
            ]),
        ]);

        $handler($this->makeCommand('evt-5', 'pkg-3', 'entregado'));

        $this->assertSame(1, $publisher->published);
        $this->assertContains('delivery_orders_completed', $kpi->names);
    }

    public function test_no_publica_evento_consolidado_si_falta_contexto_de_entrega(): void
    {
        $publisher = $this->makePublisherSpy();
        $kpi = $this->makeKpiSpy();

        $itemRepo = $this->makeItemRepoWithRows([
            (object) ['id' => 'item-4', 'op_id' => 'op-4', 'delivery_status' => 'en_ruta', 'entrega_id' => null, 'contrato_id' => null, 'ventana_entrega_id' => null],
        ]);

        $handler = $this->buildHandler([
            'kpi' => $kpi,
            'itemRepo' => $itemRepo,
            'publisher' => $publisher,
            'progressSync' => $this->makeProgressSyncReturning([
                'total_packages' => 1, 'completed_packages' => 1, 'failed_packages' => 0,
                'pending_packages' => 0, 'all_completed_at' => null,
                'entrega_id' => null, 'contrato_id' => null, 'calendario_id' => null,
            ]),
        ]);

        $handler($this->makeCommand('evt-6', 'pkg-4', 'entregado'));

        // El evento inconsistencia SI se publica, pero delivery_orders_completed NO debe incrementarse
        $this->assertNotContains('delivery_orders_completed', $kpi->names);
        $this->assertContains('alert_missing_delivery_context', $kpi->names);
    }

    public function test_missing_op_id_dispara_alerta(): void
    {
        $kpi = $this->makeKpiSpy();

        $itemRepo = $this->makeItemRepoWithRows([
            (object) ['id' => 'item-5', 'op_id' => null, 'delivery_status' => null, 'entrega_id' => null, 'contrato_id' => null, 'ventana_entrega_id' => null],
        ]);

        $handler = $this->buildHandler(['kpi' => $kpi, 'itemRepo' => $itemRepo]);

        $handler($this->makeCommand('evt-7', 'pkg-5', 'en_ruta'));

        $this->assertContains('alert_missing_op_id', $kpi->names);
    }

    public function test_normaliza_evidencia_invalida_sin_error(): void
    {
        $history = $this->makeHistoryRepo();

        $handler = $this->buildHandler([
            'itemRepo' => $this->makeItemRepoWithRows([]),
            'historyRepo' => $history,
        ]);

        $handler(new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            'evt-norm', 'pkg-norm', 'delivered', null, null,
            ['url' => 123, 'geo' => 'not-array'],
            ['source' => 'coverage']
        ));

        $this->assertSame(1, $history->insertCount);
    }

    // -------------------------------------------------------------------------
    // Builder (array overrides keeps param count at 1)
    // -------------------------------------------------------------------------

    /** @param array<string,mixed> $overrides */
    private function buildHandler(array $overrides = []): ActualizarEstadoPaqueteDesdeLogisticaHandler
    {
        $itemRepo = $overrides['itemRepo'] ?? $this->makeItemRepoWithRows([]);

        return new ActualizarEstadoPaqueteDesdeLogisticaHandler(
            evidenciaRepository: new class implements EntregaEvidenciaRepositoryInterface
            {
                public function upsertByEventId(string $eventId, array $data): void
                {
                    // intentionally empty — test stub
                }
            },
            kpiRepository: $overrides['kpi'] ?? $this->makeKpiSpy(),
            transactionAggregate: $this->tx(),
            eventPublisher: $overrides['publisher'] ?? new class implements DomainEventPublisherInterface
            {
                public function publish(array $events, mixed $aggregateId): void
                {
                    // intentionally empty — test stub
                }
            },
            statusMapper: new DeliveryStatusMapper,
            historyRepository: $overrides['historyRepo'] ?? $this->makeHistoryRepo(),
            trackingRepository: $overrides['trackingRepo'] ?? $this->makeTrackingRepo(),
            progressRepository: $overrides['progressRepo'] ?? $this->makeProgressRepoMarkingRows(0),
            inconsistencyRepository: $overrides['inconsistencyRepo'] ?? $this->makeInconsistencyRepo(),
            itemDespachoRepository: $itemRepo,
            ordenProduccionRepository: new class implements OrdenProduccionRepositoryInterface
            {
                public function byId(?string $id): ?\App\Domain\Produccion\Aggregate\OrdenProduccion
                {
                    return null;
                }

                public function save(\App\Domain\Produccion\Aggregate\OrdenProduccion $op): string
                {
                    return '';
                }

                public function markEntregaCompletada(string $opId, DateTimeImmutable $completedAt): void
                {
                    // intentionally empty — test stub
                }
            },
            backfiller: new DeliveryContextBackfiller(
                $itemRepo,
                new class implements VentanaEntregaRepositoryInterface
                {
                    public function byId(string|int $id): ?VentanaEntrega
                    {
                        return null;
                    }

                    public function save(VentanaEntrega $v): string
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
                }
            ),
            progressSync: $overrides['progressSync'] ?? $this->makeProgressSyncReturning([
                'total_packages' => 0, 'completed_packages' => 0, 'failed_packages' => 0,
                'pending_packages' => 0, 'all_completed_at' => null,
                'entrega_id' => null, 'contrato_id' => null, 'calendario_id' => null,
            ]),
            logger: new NullLogger
        );
    }

    // -------------------------------------------------------------------------
    // Spy / stub factories
    // -------------------------------------------------------------------------

    private function tx(): TransactionAggregate
    {
        return new TransactionAggregate(new class implements TransactionManagerInterface
        {
            public function run(callable $callback): mixed
            {
                return $callback();
            }

            public function afterCommit(callable $callback): void
            {
                // intentionally empty — test stub
            }
        });
    }

    private function makeKpiSpy(): object
    {
        return new class implements KpiRepositoryInterface
        {
            /** @var string[] */
            public array $names = [];

            public function increment(string $name, int $by = 1): void
            {
                $this->names[] = $name;
            }
        };
    }

    private function makePublisherSpy(): object
    {
        return new class implements DomainEventPublisherInterface
        {
            public int $published = 0;

            public function publish(array $events, mixed $aggregateId): void
            {
                $this->published += count($events);
            }
        };
    }

    private function makeHistoryRepo(): object
    {
        return new class implements PackageDeliveryHistoryRepositoryInterface
        {
            public int $insertCount = 0;

            public int $updateCount = 0;

            private ?object $stored = null;

            public function findByEventId(string $eventId): ?object
            {
                return $this->stored;
            }

            public function insert(array $data): void
            {
                $this->insertCount++;
                $this->stored = (object) $data;
            }

            public function updateByEventId(string $eventId, array $data): void
            {
                $this->updateCount++;
            }
        };
    }

    private function makeTrackingRepo(): object
    {
        return new class implements PackageDeliveryTrackingRepositoryInterface
        {
            public int $upsertCount = 0;

            public function findByPackageId(string $packageId): ?object
            {
                return null;
            }

            public function upsertByPackageId(string $packageId, array $values): void
            {
                $this->upsertCount++;
            }
        };
    }

    private function makeInconsistencyRepo(): object
    {
        return new class implements DeliveryInconsistencyQueueRepositoryInterface
        {
            public int $insertCount = 0;

            /** @var array<string,bool> */
            private array $stored = [];

            public function existsByEventIdAndReason(string $eventId, string $reason): bool
            {
                return isset($this->stored["{$eventId}:{$reason}"]);
            }

            public function insert(array $data): void
            {
                $this->insertCount++;
                $this->stored["{$data['event_id']}:{$data['reason']}"] = true;
            }

            public function updateByEventIdAndReason(string $eventId, string $reason, array $data): void
            {
                // intentionally empty — deduplication update, not asserted in these tests
            }
        };
    }

    private function makeProgressRepoMarkingRows(int $rowsMarked): object
    {
        return new class($rowsMarked) implements OrderDeliveryProgressRepositoryInterface
        {
            public function __construct(private int $rows) {}

            public function findByOpId(string $opId): ?object
            {
                return null;
            }

            public function upsertByOpId(string $opId, array $values): void
            {
                // intentionally empty — test stub
            }

            public function markCompletionIfNotSet(string $opId, string $completionEventId, string $allCompletedAt): int
            {
                return $this->rows;
            }
        };
    }

    private function makeItemRepoWithRows(array $rows): object
    {
        return new class($rows) implements ItemDespachoRepositoryInterface
        {
            /** @var array<int, array> */
            public array $updatedFields = [];

            public function __construct(private array $rows) {}

            public function byId(string $id): ?ItemDespacho
            {
                return null;
            }

            public function save(ItemDespacho $item): void
            {
                // intentionally empty — test stub
            }

            public function findDeliveryRowsByPaqueteId(string $packageId): array
            {
                return $this->rows;
            }

            public function findBackfillRowsByPaqueteId(string $packageId): array
            {
                return [];
            }

            public function updateDeliveryFields(string $id, array $fields): void
            {
                $this->updatedFields[] = $fields;
            }

            public function updateDeliveryContext(string $id, array $fields): void
            {
                // intentionally empty — test stub
            }

            public function countDistinctPaquetesByOpId(string $opId): int
            {
                return 0;
            }

            public function countDistinctPaquetesByOpIdAndStatus(string $opId, string $status): int
            {
                return 0;
            }

            public function findFirstEntregaIdByOpId(string $opId): ?string
            {
                return null;
            }

            public function findFirstContratoIdByOpId(string $opId): ?string
            {
                return null;
            }

            public function findCalendarioIdByOpId(string $opId): ?string
            {
                return null;
            }
        };
    }

    private function makeProgressSyncReturning(array $projection): OrderDeliveryProgressSync
    {
        $itemRepo = $this->makeItemRepoWithRows([]);
        $progressRepo = $this->makeProgressRepoMarkingRows(0);
        $sync = $this->getMockBuilder(OrderDeliveryProgressSync::class)
            ->setConstructorArgs([$itemRepo, $progressRepo])
            ->onlyMethods(['syncAndGetProjection'])
            ->getMock();
        $sync->method('syncAndGetProjection')->willReturn($projection);

        return $sync;
    }

    private function makeCommand(string $eventId, string $packageId, string $status): ActualizarEstadoPaqueteDesdeLogisticaCommand
    {
        return new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            $eventId, $packageId, $status, null, null, null, ['source' => 'test']
        );
    }
}

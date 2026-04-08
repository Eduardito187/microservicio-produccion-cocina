<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion\Service;

use App\Application\Produccion\Repository\OrderDeliveryProgressRepositoryInterface;
use App\Application\Produccion\Service\OrderDeliveryProgressSync;
use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Domain\Produccion\ValueObjects\OccurredOn;
use DateTimeImmutable;
use Tests\TestCase;

/**
 * @class OrderDeliveryProgressSyncTest
 */
class OrderDeliveryProgressSyncTest extends TestCase
{
    public function test_sync_and_get_projection_retorna_projection_completa(): void
    {
        $upsertedValues = null;

        $itemRepo = $this->makeItemRepo(
            total: 2,
            confirmed: 1,
            failed: 0,
            firstEntregaId: '123e4567-e89b-12d3-a456-426614174010',
            firstContratoId: '123e4567-e89b-12d3-a456-426614174011',
            calendarioId: 'cal-1'
        );

        $progressRepo = $this->makeProgressRepo(
            existing: null,
            onUpsert: function (string $opId, array $values) use (&$upsertedValues): void {
                $upsertedValues = $values;
            }
        );

        $sync = new OrderDeliveryProgressSync($itemRepo, $progressRepo);
        $projection = $sync->syncAndGetProjection('op-1', null);

        $this->assertSame(2, $projection['total_packages']);
        $this->assertSame(1, $projection['completed_packages']);
        $this->assertSame(0, $projection['failed_packages']);
        $this->assertSame(1, $projection['pending_packages']);
        $this->assertSame('cal-1', $projection['calendario_id']);
        $this->assertSame('123e4567-e89b-12d3-a456-426614174010', $projection['entrega_id']);
        $this->assertSame('123e4567-e89b-12d3-a456-426614174011', $projection['contrato_id']);
        $this->assertNotNull($upsertedValues);
        $this->assertArrayHasKey('id', $upsertedValues);
    }

    public function test_sync_and_get_projection_marca_all_completed_at_cuando_todos_confirmados(): void
    {
        $itemRepo = $this->makeItemRepo(total: 1, confirmed: 1, failed: 0, firstEntregaId: null, firstContratoId: null, calendarioId: null);
        $progressRepo = $this->makeProgressRepo(existing: null, onUpsert: function (): void {});

        $sync = new OrderDeliveryProgressSync($itemRepo, $progressRepo);
        $projection = $sync->syncAndGetProjection('op-1', new OccurredOn(new DateTimeImmutable('2026-04-06T12:00:00+00:00')));

        $this->assertNotNull($projection['all_completed_at']);
    }

    public function test_sync_and_get_projection_preserva_all_completed_at_existente(): void
    {
        $existingProgress = (object) ['all_completed_at' => '2026-04-05 10:00:00'];

        $itemRepo = $this->makeItemRepo(total: 1, confirmed: 1, failed: 0, firstEntregaId: null, firstContratoId: null, calendarioId: null);
        $progressRepo = $this->makeProgressRepo(existing: $existingProgress, onUpsert: function (): void {});

        $sync = new OrderDeliveryProgressSync($itemRepo, $progressRepo);
        $projection = $sync->syncAndGetProjection('op-1', new OccurredOn(new DateTimeImmutable('now')));

        $this->assertSame('2026-04-05 10:00:00', $projection['all_completed_at']);
    }

    public function test_sync_and_get_projection_ignora_entrega_id_invalido(): void
    {
        $itemRepo = $this->makeItemRepo(total: 1, confirmed: 1, failed: 0, firstEntregaId: 'no-es-uuid', firstContratoId: 'tampoco-uuid', calendarioId: null);
        $progressRepo = $this->makeProgressRepo(existing: null, onUpsert: function (): void {});

        $sync = new OrderDeliveryProgressSync($itemRepo, $progressRepo);
        $projection = $sync->syncAndGetProjection('op-1', null);

        $this->assertNull($projection['entrega_id']);
        $this->assertNull($projection['contrato_id']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeItemRepo(int $total, int $confirmed, int $failed, ?string $firstEntregaId, ?string $firstContratoId, ?string $calendarioId): ItemDespachoRepositoryInterface
    {
        return new class($total, $confirmed, $failed, $firstEntregaId, $firstContratoId, $calendarioId) implements ItemDespachoRepositoryInterface
        {
            public function __construct(
                private int $total,
                private int $confirmed,
                private int $failed,
                private ?string $entregaId,
                private ?string $contratoId,
                private ?string $calendarioId
            ) {}

            public function byId(string $id): ?ItemDespacho
            {
                return null;
            }

            public function save(ItemDespacho $item): void {}

            public function findDeliveryRowsByPaqueteId(string $packageId): array
            {
                return [];
            }

            public function findBackfillRowsByPaqueteId(string $packageId): array
            {
                return [];
            }

            public function updateDeliveryFields(string $id, array $fields): void {}

            public function updateDeliveryContext(string $id, array $fields): void {}

            public function countDistinctPaquetesByOpId(string $opId): int
            {
                return $this->total;
            }

            public function countDistinctPaquetesByOpIdAndStatus(string $opId, string $status): int
            {
                return match ($status) {
                    'confirmada' => $this->confirmed,
                    'fallida' => $this->failed,
                    default => 0,
                };
            }

            public function findFirstEntregaIdByOpId(string $opId): ?string
            {
                return $this->entregaId;
            }

            public function findFirstContratoIdByOpId(string $opId): ?string
            {
                return $this->contratoId;
            }

            public function findCalendarioIdByOpId(string $opId): ?string
            {
                return $this->calendarioId;
            }
        };
    }

    private function makeProgressRepo(?object $existing, callable $onUpsert): OrderDeliveryProgressRepositoryInterface
    {
        return new class($existing, $onUpsert) implements OrderDeliveryProgressRepositoryInterface
        {
            public function __construct(private ?object $existing, private $onUpsert) {}

            public function findByOpId(string $opId): ?object
            {
                return $this->existing;
            }

            public function upsertByOpId(string $opId, array $values): void
            {
                ($this->onUpsert)($opId, $values);
            }

            public function markCompletionIfNotSet(string $opId, string $completionEventId, string $allCompletedAt): int
            {
                return 1;
            }
        };
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion\Service;

use App\Application\Produccion\Service\DeliveryContextBackfiller;
use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use DateTimeImmutable;
use Tests\TestCase;

/**
 * @class DeliveryContextBackfillerTest
 */
class DeliveryContextBackfillerTest extends TestCase
{
    public function test_backfill_rellena_entrega_id_y_contrato_id_desde_ventana(): void
    {
        $updatedContexts = [];

        $itemRepo = $this->makeItemDespachoRepo(
            backfillRows: [
                (object) ['id' => 'item-1', 'ventana_entrega_id' => 'ven-1', 'entrega_id' => null, 'contrato_id' => null],
            ],
            onUpdateContext: function (string $id, array $fields) use (&$updatedContexts): void {
                $updatedContexts[$id] = $fields;
            }
        );

        $ventanaRepo = $this->makeVentanaRepo(
            'ven-1',
            new VentanaEntrega('ven-1', new DateTimeImmutable, new DateTimeImmutable, 'entrega-uuid', 'contrato-uuid')
        );

        $backfiller = new DeliveryContextBackfiller($itemRepo, $ventanaRepo);
        $backfiller->backfill('pkg-1');

        $this->assertArrayHasKey('item-1', $updatedContexts);
        $this->assertSame('entrega-uuid', $updatedContexts['item-1']['entrega_id']);
        $this->assertSame('contrato-uuid', $updatedContexts['item-1']['contrato_id']);
    }

    public function test_backfill_omite_fila_que_ya_tiene_ambos_ids(): void
    {
        $updateCalled = false;

        $itemRepo = $this->makeItemDespachoRepo(
            backfillRows: [
                (object) ['id' => 'item-1', 'ventana_entrega_id' => 'ven-1', 'entrega_id' => 'e-existing', 'contrato_id' => 'c-existing'],
            ],
            onUpdateContext: function () use (&$updateCalled): void {
                $updateCalled = true;
            }
        );

        $ventanaRepo = $this->makeVentanaRepo('ven-1', null);

        $backfiller = new DeliveryContextBackfiller($itemRepo, $ventanaRepo);
        $backfiller->backfill('pkg-1');

        $this->assertFalse($updateCalled);
    }

    public function test_backfill_omite_fila_sin_ventana_entrega_id(): void
    {
        $updateCalled = false;

        $itemRepo = $this->makeItemDespachoRepo(
            backfillRows: [
                (object) ['id' => 'item-1', 'ventana_entrega_id' => null, 'entrega_id' => null, 'contrato_id' => null],
            ],
            onUpdateContext: function () use (&$updateCalled): void {
                $updateCalled = true;
            }
        );

        $ventanaRepo = $this->makeVentanaRepo('any', null);

        $backfiller = new DeliveryContextBackfiller($itemRepo, $ventanaRepo);
        $backfiller->backfill('pkg-1');

        $this->assertFalse($updateCalled);
    }

    public function test_backfill_omite_fila_cuando_ventana_no_existe(): void
    {
        $updateCalled = false;

        $itemRepo = $this->makeItemDespachoRepo(
            backfillRows: [
                (object) ['id' => 'item-1', 'ventana_entrega_id' => 'ven-missing', 'entrega_id' => null, 'contrato_id' => null],
            ],
            onUpdateContext: function () use (&$updateCalled): void {
                $updateCalled = true;
            }
        );

        $ventanaRepo = new class implements VentanaEntregaRepositoryInterface
        {
            public function byId(string|int $id): ?VentanaEntrega
            {
                throw new EntityNotFoundException('not found');
            }

            public function save(VentanaEntrega $ventanaEntrega): string
            {
                return '';
            }

            public function list(): array
            {
                return [];
            }

            public function listVigentes(): array
            {
                return [];
            }

            public function byPacienteId(string $pacienteId): array
            {
                return [];
            }

            public function byCalendarioId(string $calendarioId): array
            {
                return [];
            }

            public function delete(string|int $id): void {}

            public function desactivar(string $id): void {}
        };

        $backfiller = new DeliveryContextBackfiller($itemRepo, $ventanaRepo);
        $backfiller->backfill('pkg-1');

        $this->assertFalse($updateCalled);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeItemDespachoRepo(array $backfillRows, callable $onUpdateContext): ItemDespachoRepositoryInterface
    {
        return new class($backfillRows, $onUpdateContext) implements ItemDespachoRepositoryInterface
        {
            public function __construct(private array $rows, private $onUpdate) {}

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
                return $this->rows;
            }

            public function updateDeliveryFields(string $id, array $fields): void {}

            public function updateDeliveryContext(string $id, array $fields): void
            {
                ($this->onUpdate)($id, $fields);
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

    private function makeVentanaRepo(string $expectedId, ?VentanaEntrega $ventana): VentanaEntregaRepositoryInterface
    {
        return new class($expectedId, $ventana) implements VentanaEntregaRepositoryInterface
        {
            public function __construct(private string $id, private ?VentanaEntrega $ventana) {}

            public function byId(string|int $id): ?VentanaEntrega
            {
                if ($id !== $this->id) {
                    throw new EntityNotFoundException('not found');
                }

                return $this->ventana;
            }

            public function save(VentanaEntrega $ventanaEntrega): string
            {
                return '';
            }

            public function list(): array
            {
                return [];
            }

            public function listVigentes(): array
            {
                return [];
            }

            public function byPacienteId(string $pacienteId): array
            {
                return [];
            }

            public function byCalendarioId(string $calendarioId): array
            {
                return [];
            }

            public function delete(string|int $id): void {}

            public function desactivar(string $id): void {}
        };
    }
}

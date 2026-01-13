<?php

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Application\Produccion\Command\DespachadorOP;
use App\Application\Produccion\Command\PlanificarOP;
use App\Domain\Produccion\Aggregate\OrdenProduccion;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\Entity\Products;
use App\Domain\Produccion\Enum\EstadoOP;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class OrdenProduccionBatchWorkflowTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_generar_batches_creates_one_batch_per_item_and_uses_item_product_id(): void
    {
        $op = OrdenProduccion::reconstitute(
            id: 123,
            fecha: new DateTimeImmutable('2025-11-04'),
            sucursalId: 'SCZ-001',
            estado: EstadoOP::CREADA,
            items: [],
            batches: [],
            itemsDespacho: []
        );

        $op->agregarItems([
            ['sku' => 'PIZZA-PEP', 'qty' => 2], ['sku' => 'PIZZA-MARG', 'qty' => 1]
        ]);

        $op->items()[0]->loadProduct(new Products(10, 'PIZZA-PEP', 10.0, 0.0));
        $op->items()[1]->loadProduct(new Products(20, 'PIZZA-MARG', 12.0, 0.0));

        $cmd = new PlanificarOP([
            'ordenProduccionId' => 123, 'estacionId' => 1, 'recetaVersionId' => 7, 'porcionId' => 3
        ]);

        $op->generarBatches($cmd);

        $batches = $op->batches();
        $this->assertCount(2, $batches);

        $this->assertSame(10, $batches[0]->productoId);
        $this->assertSame(1, $batches[0]->estacionId);
        $this->assertSame(7, $batches[0]->recetaVersionId);
        $this->assertSame(3, $batches[0]->porcionId);
        $this->assertSame(EstadoPlanificado::PROGRAMADO, $batches[0]->estado);
        $this->assertSame(1, $batches[0]->posicion);

        $this->assertSame(20, $batches[1]->productoId);
        $this->assertSame(2, $batches[0]->qty->value);
        $this->assertSame(2, $batches[0]->cantPlanificada);
    }

    /**
     * @inheritDoc
     */
    public function test_procesar_and_despachar_batches_transitions_all_batches(): void
    {
        $op = OrdenProduccion::reconstitute(
            id: 123,
            fecha: new DateTimeImmutable('2025-11-04'),
            sucursalId: 'SCZ-001',
            estado: EstadoOP::CREADA,
            items: [],
            batches: [],
            itemsDespacho: []
        );

        $op->agregarItems([
            ['sku' => 'PIZZA-PEP', 'qty' => 1],
        ]);
        $op->items()[0]->loadProduct(new Products(10, 'PIZZA-PEP', 10.0, 0.0));

        $op->generarBatches(new PlanificarOP([
            'ordenProduccionId' => 123,
            'estacionId' => 1,
            'recetaVersionId' => 7,
            'porcionId' => 3,
        ]));

        $op->procesarBatches();
        $this->assertSame(EstadoPlanificado::PROCESANDO, $op->batches()[0]->estado);
        $this->assertSame(1, $op->batches()[0]->cantProducida);

        $op->despacharBatches();
        $this->assertSame(EstadoPlanificado::DESPACHADO, $op->batches()[0]->estado);
    }

    /**
     * @inheritDoc
     */
    public function test_generar_items_despacho_creates_one_item_per_order_item(): void
    {
        $op = OrdenProduccion::reconstitute(
            id: 123,
            fecha: new DateTimeImmutable('2025-11-04'),
            sucursalId: 'SCZ-001',
            estado: EstadoOP::CREADA,
            items: [],
            batches: [],
            itemsDespacho: []
        );

        $op->agregarItems([
            ['sku' => 'PIZZA-PEP', 'qty' => 1],
            ['sku' => 'PIZZA-MARG', 'qty' => 1],
        ]);
        $op->items()[0]->loadProduct(new Products(10, 'PIZZA-PEP', 10.0, 0.0));
        $op->items()[1]->loadProduct(new Products(20, 'PIZZA-MARG', 10.0, 0.0));

        $op->generarItemsDespacho(new DespachadorOP([
            'ordenProduccionId' => 123,
            'itemsDespacho' => [
                ['sku' => 'PIZZA-PEP', 'recetaVersionId' => 1],
                ['sku' => 'PIZZA-MARG', 'recetaVersionId' => 1],
            ],
            'pacienteId' => 1,
            'direccionId' => 1,
            'ventanaEntrega' => 1,
        ]));

        $items = $op->itemsDespacho();
        $this->assertCount(2, $items);
        $this->assertSame(10, $items[0]->productId);
        $this->assertSame(20, $items[1]->productId);
    }
}
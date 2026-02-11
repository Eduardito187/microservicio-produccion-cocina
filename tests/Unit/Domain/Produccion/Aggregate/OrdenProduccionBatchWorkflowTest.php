<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\OrdenProduccion;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\Entity\Products;
use App\Domain\Produccion\Enum\EstadoOP;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

/**
 * @class OrdenProduccionBatchWorkflowTest
 * @package Tests\Unit\Domain\Produccion\Aggregate
 */
class OrdenProduccionBatchWorkflowTest extends TestCase
{
    /**
     * @return void
     */
    public function test_generar_batches_creates_one_batch_per_item_and_uses_item_product_id(): void
    {
        $ordenProduccion = OrdenProduccion::reconstitute(
            123, new DateTimeImmutable('2025-11-04'), 'SCZ-001', EstadoOP::CREADA, [], [], []
        );

        $ordenProduccion->agregarItems([['sku' => 'PIZZA-PEP', 'qty' => 2], ['sku' => 'PIZZA-MARG', 'qty' => 1]]);
        $ordenProduccion->items()[0]->loadProduct(new Products(10, 'PIZZA-PEP', 10.0, 0.0));
        $ordenProduccion->items()[1]->loadProduct(new Products(20, 'PIZZA-MARG', 12.0, 0.0));

        $ordenProduccion->generarBatches(1, 7, 3);
        $batches = $ordenProduccion->batches();
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
        $ordenProduccion = OrdenProduccion::reconstitute(
            123, new DateTimeImmutable('2025-11-04'), 'SCZ-001', EstadoOP::CREADA, [], [], []
        );
        $ordenProduccion->agregarItems([['sku' => 'PIZZA-PEP', 'qty' => 1]]);
        $ordenProduccion->items()[0]->loadProduct(new Products(10, 'PIZZA-PEP', 10.0, 0.0));
        $ordenProduccion->generarBatches(1, 7, 3);

        $ordenProduccion->procesarBatches();
        $this->assertSame(EstadoPlanificado::PROCESANDO, $ordenProduccion->batches()[0]->estado);
        $this->assertSame(1, $ordenProduccion->batches()[0]->cantProducida);

        $ordenProduccion->despacharBatches();
        $this->assertSame(EstadoPlanificado::DESPACHADO, $ordenProduccion->batches()[0]->estado);
    }

    /**
     * @inheritDoc
     */
    public function test_generar_items_despacho_creates_one_item_per_order_item(): void
    {
        $ordenProduccion = OrdenProduccion::reconstitute(
            123, new DateTimeImmutable('2025-11-04'), 'SCZ-001', EstadoOP::CREADA, [], [], []
        );
        $ordenProduccion->agregarItems([
            ['sku' => 'PIZZA-PEP', 'qty' => 1], ['sku' => 'PIZZA-MARG', 'qty' => 1]
        ]);
        $ordenProduccion->items()[0]->loadProduct(new Products(10, 'PIZZA-PEP', 10.0, 0.0));
        $ordenProduccion->items()[1]->loadProduct(new Products(20, 'PIZZA-MARG', 10.0, 0.0));

        $ordenProduccion->generarItemsDespacho(
            [
                ['sku' => 'PIZZA-PEP', 'recetaVersionId' => 1],
                ['sku' => 'PIZZA-MARG', 'recetaVersionId' => 1],
            ],
            1,
            1,
            1
        );

        $items = $ordenProduccion->itemsDespacho();
        $this->assertCount(2, $items);
        $this->assertSame(10, $items[0]->productId);
        $this->assertSame(20, $items[1]->productId);
    }
}

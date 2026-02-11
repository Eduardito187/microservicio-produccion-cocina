<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Produccion\Aggregate\OrdenProduccion;
use App\Domain\Produccion\Enum\EstadoOP;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use DomainException;

/**
 * @class OrdenProduccionTest
 * @package Tests\Unit\Domain\Produccion\Aggregate
 */
class OrdenProduccionTest extends TestCase
{
    /**
     * @return void
     */
    public function test_crear_inicializa_la_op_en_estado_creada_y_registra_evento(): void
    {
        $fecha = new DateTimeImmutable('2025-01-01');
        $ordenProduccion = OrdenProduccion::crear($fecha, 'SUC1');
        $this->assertSame(EstadoOP::CREADA, $ordenProduccion->estado());
        $this->assertSame('SUC1', $ordenProduccion->sucursalId());

        $events = $ordenProduccion->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrdenProduccionCreada::class, $events[0]);
    }

    /**
     * @return void
     */
    public function test_agregar_items_construye_orden_items_desde_array(): void
    {
        $fecha = new DateTimeImmutable('2025-01-01');
        $ordenProduccion = OrdenProduccion::crear($fecha, 'SUC1');
        $items = [['sku' => 'ABC', 'qty' => 3], ['sku' => 'XYZ', 'qty' => 5]];

        $ordenProduccion->agregarItems($items);
        $this->assertCount(2, $ordenProduccion->items());
        $this->assertSame('ABC', $ordenProduccion->items()[0]->sku()->value());
        $this->assertSame(3, $ordenProduccion->items()[0]->qty()->value());
    }

    /**
     * @return void
     */
    public function test_agregar_items_falla_si_estado_no_es_creada(): void
    {
        $fecha = new DateTimeImmutable('2025-01-01');
        $ordenProduccion = OrdenProduccion::crear($fecha, 'SUC1');

        $ordenProduccion->planificar();
        $this->expectException(DomainException::class);
        $ordenProduccion->agregarItems([['sku' => 'ABC', 'qty' => 3]]);
    }

    /**
     * @return void
     */
    public function test_flujo_de_estados_planificar_procesar_cerrar(): void
    {
        $fecha = new DateTimeImmutable('2025-01-01');
        $ordenProduccion = OrdenProduccion::crear($fecha, 'SUC1');
        $this->assertNull($ordenProduccion->id());

        $ordenProduccion->planificar();
        $this->assertSame(EstadoOP::PLANIFICADA, $ordenProduccion->estado());

        $ordenProduccion->procesar();
        $this->assertSame(EstadoOP::EN_PROCESO, $ordenProduccion->estado());

        $ordenProduccion->cerrar();
        $this->assertSame(EstadoOP::CERRADA, $ordenProduccion->estado());
    }
}

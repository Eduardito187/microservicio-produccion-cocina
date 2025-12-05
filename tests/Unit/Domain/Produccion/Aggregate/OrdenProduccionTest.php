<?php

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Produccion\Aggregate\OrdenProduccion;
use App\Domain\Produccion\Enum\EstadoOP;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use DomainException;

class OrdenProduccionTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_crear_inicializa_la_op_en_estado_creada_y_registra_evento(): void
    {
        $fecha = new DateTimeImmutable('2025-01-01');
        $op = OrdenProduccion::crear($fecha, 'SUC1');
        $this->assertSame(EstadoOP::CREADA, $op->estado());
        $this->assertSame('SUC1', $op->sucursalId());
        $events = $op->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrdenProduccionCreada::class, $events[0]);
    }

    /**
     * @inheritDoc
     */
    public function test_agregar_items_construye_orden_items_desde_array(): void
    {
        $fecha = new DateTimeImmutable('2025-01-01');
        $op = OrdenProduccion::crear($fecha, 'SUC1');
        $items = [
            ['sku' => 'ABC', 'qty' => 3],
            ['sku' => 'XYZ', 'qty' => 5],
        ];
        $op->agregarItems($items);
        $this->assertCount(2, $op->items());
        $this->assertSame('ABC', $op->items()[0]->sku()->value());
        $this->assertSame(3, $op->items()[0]->qty()->value());
    }

    /**
     * @inheritDoc
     */
    public function test_agregar_items_falla_si_estado_no_es_creada(): void
    {
        $fecha = new DateTimeImmutable('2025-01-01');
        $op = OrdenProduccion::crear($fecha, 'SUC1');
        $op->planificar();
        $this->expectException(DomainException::class);
        $op->agregarItems([
            ['sku' => 'ABC', 'qty' => 3],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function test_flujo_de_estados_planificar_procesar_cerrar(): void
    {
        $fecha = new DateTimeImmutable('2025-01-01');
        $op = OrdenProduccion::crear($fecha, 'SUC1');
        $op->planificar();
        $this->assertSame(EstadoOP::PLANIFICADA, $op->estado());
        $op->procesar();
        $this->assertSame(EstadoOP::EN_PROCESO, $op->estado());
        $op->cerrar();
        $this->assertSame(EstadoOP::CERRADA, $op->estado());
    }
}
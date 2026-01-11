<?php

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\Events\OrdenProduccionPlanificada;
use App\Domain\Produccion\Events\OrdenProduccionProcesada;
use App\Domain\Produccion\Events\OrdenProduccionCerrada;
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
    public function test_crear_inicia_en_estado_creada_y_registra_evento(): void
    {
        $op = OrdenProduccion::crear(new DateTimeImmutable('2025-11-04'), 'SCZ-001');

        $this->assertSame(EstadoOP::CREADA, $op->estado());
        $events = $op->pullEvents();

        $this->assertCount(1, $events);
        $this->assertSame(OrdenProduccionCreada::class, $events[0]->name());
    }

    /**
     * @inheritDoc
     */
    public function test_agregar_items_solo_permitido_en_creada(): void
    {
        $op = OrdenProduccion::crear(new DateTimeImmutable('2025-11-04'), 'SCZ-001');
        $op->agregarItems([
            ['sku' => 'PIZZA-PEP', 'qty' => 2],
            ['sku' => 'PIZZA-MARG', 'qty' => 1],
        ]);

        $this->assertCount(2, $op->items());
        $this->assertSame('PIZZA-PEP', (string) $op->items()[0]->sku()->value);

        $op->planificar();

        $this->expectException(DomainException::class);
        $op->agregarItems([
            ['sku' => 'SKU3', 'qty' => 1],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function test_transiciones_planificar_procesar_cerrar_registran_eventos(): void
    {
        $op = OrdenProduccion::crear(new DateTimeImmutable('2025-11-04'), 'SCZ-001');
        $op->agregarItems([
            ['sku' => 'PIZZA-PEP', 'qty' => 1],
        ]);

        // limpiamos el evento de creación para enfocarnos en transiciones
        $op->pullEvents();

        $op->planificar();
        $this->assertSame(EstadoOP::PLANIFICADA, $op->estado());

        $op->procesar();
        $this->assertSame(EstadoOP::EN_PROCESO, $op->estado());

        $op->cerrar();
        $this->assertSame(EstadoOP::CERRADA, $op->estado());

        $events = $op->pullEvents();
        $this->assertCount(3, $events);
        $this->assertSame(OrdenProduccionPlanificada::class, $events[0]->name());
        $this->assertSame(OrdenProduccionProcesada::class, $events[1]->name());
        $this->assertSame(OrdenProduccionCerrada::class, $events[2]->name());
    }

    /**
     * @inheritDoc
     */
    public function test_no_permite_transiciones_invalidas(): void
    {
        $op = OrdenProduccion::crear(new DateTimeImmutable('2025-11-04'), 'SCZ-001');

        $this->expectException(DomainException::class);
        $op->procesar();
    }
}
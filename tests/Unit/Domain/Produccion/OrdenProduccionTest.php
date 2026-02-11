<?php
/**
 * Microservicio "Produccion y Cocina"
 */

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

/**
 * @class OrdenProduccionTest
 * @package Tests\Unit\Domain\Produccion
 */
class OrdenProduccionTest extends TestCase
{
    /**
     * @return void
     */
    public function test_crear_inicia_en_estado_creada_y_registra_evento(): void
    {
        $ordenProduccion = OrdenProduccion::crear(new DateTimeImmutable('2025-11-04'), 'SCZ-001');
        $this->assertSame(EstadoOP::CREADA, $ordenProduccion->estado());
        $events = $ordenProduccion->pullEvents();

        $this->assertCount(1, $events);
        $this->assertSame(OrdenProduccionCreada::class, $events[0]->name());
    }

    /**
     * @return void
     */
    public function test_agregar_items_solo_permitido_en_creada(): void
    {
        $ordenProduccion = OrdenProduccion::crear(new DateTimeImmutable('2025-11-04'), 'SCZ-001');
        $ordenProduccion->agregarItems([['sku' => 'PIZZA-PEP', 'qty' => 2], ['sku' => 'PIZZA-MARG', 'qty' => 1]]);

        $this->assertCount(2, $ordenProduccion->items());
        $this->assertSame('PIZZA-PEP', (string) $ordenProduccion->items()[0]->sku()->value);
        $ordenProduccion->planificar();

        $this->expectException(DomainException::class);
        $ordenProduccion->agregarItems([['sku' => 'SKU3', 'qty' => 1]]);
    }

    /**
     * @return void
     */
    public function test_transiciones_planificar_procesar_cerrar_registran_eventos(): void
    {
        $ordenProduccion = OrdenProduccion::crear(new DateTimeImmutable('2025-11-04'), 'SCZ-001');
        $ordenProduccion->agregarItems([['sku' => 'PIZZA-PEP', 'qty' => 1]]);

        // limpiamos el evento de creación para enfocarnos en transiciones
        $ordenProduccion->pullEvents();

        $ordenProduccion->planificar();
        $this->assertSame(EstadoOP::PLANIFICADA, $ordenProduccion->estado());

        $ordenProduccion->procesar();
        $this->assertSame(EstadoOP::EN_PROCESO, $ordenProduccion->estado());

        $ordenProduccion->cerrar();
        $this->assertSame(EstadoOP::CERRADA, $ordenProduccion->estado());

        $events = $ordenProduccion->pullEvents();
        $this->assertCount(3, $events);
        $this->assertSame(OrdenProduccionPlanificada::class, $events[0]->name());
        $this->assertSame(OrdenProduccionProcesada::class, $events[1]->name());
        $this->assertSame(OrdenProduccionCerrada::class, $events[2]->name());
    }

    /**
     * @return void
     */
    public function test_no_permite_transiciones_invalidas(): void
    {
        $ordenProduccion = OrdenProduccion::crear(new DateTimeImmutable('2025-11-04'), 'SCZ-001');
        $this->expectException(DomainException::class);
        $ordenProduccion->procesar();
    }
}

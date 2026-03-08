<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\Aggregate\ProduccionBatch;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\ValueObjects\Qty;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @class ProduccionBatchTest
 */
class ProduccionBatchTest extends TestCase
{
    public function test_procesar_y_despachar_cambian_estado_y_cantidades(): void
    {
        $batch = new ProduccionBatch(
            1,
            10,
            99,
            1,
            5,
            0,
            0,
            EstadoPlanificado::PROGRAMADO,
            0,
            new Qty(5),
            1,
            []
        );

        $batch->procesar();
        $this->assertSame(EstadoPlanificado::PROCESANDO, $batch->estado);
        $this->assertSame(5, $batch->cantProducida);

        $batch->despachar();
        $this->assertSame(EstadoPlanificado::DESPACHADO, $batch->estado);
    }

    public function test_no_permite_despachar_si_no_esta_procesando(): void
    {
        $batch = new ProduccionBatch(
            1,
            10,
            99,
            1,
            5,
            0,
            0,
            EstadoPlanificado::PROGRAMADO,
            0,
            new Qty(5),
            1,
            []
        );

        $this->expectException(DomainException::class);
        $batch->despachar();
    }
}

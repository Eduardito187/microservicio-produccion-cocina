<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\ProduccionBatch;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\ValueObjects\Qty;
use PHPUnit\Framework\TestCase;
use DomainException;

/**
 * @class ProduccionBatchInvalidProcessTest
 * @package Tests\Unit\Domain\Produccion\Aggregate
 */
class ProduccionBatchInvalidProcessTest extends TestCase
{
    /**
     * @return void
     */
    public function test_no_permite_procesar_si_no_esta_planificado(): void
    {
        $batch = ProduccionBatch::crear(
            1, 1, 1, 1, 1, 1, 1, 1, 1, EstadoPlanificado::PROCESANDO, 10, new Qty(1), 1, []
        );

        $this->expectException(DomainException::class);
        $batch->procesar();
    }
}

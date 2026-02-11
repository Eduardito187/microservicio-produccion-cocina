<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\ProduccionBatchCreado;
use App\Domain\Produccion\Aggregate\ProduccionBatch;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\ValueObjects\Qty;
use PHPUnit\Framework\TestCase;

/**
 * @class ProduccionBatchCrearTest
 * @package Tests\Unit\Domain\Produccion\Aggregate
 */
class ProduccionBatchCrearTest extends TestCase
{
    /**
     * @return void
     */
    public function test_crear_records_event_and_sets_initial_state(): void
    {
        $produccionBatch = ProduccionBatch::crear(
            1, 123, 10, 2, 7, 3, 5, 0, 0, EstadoPlanificado::PROGRAMADO, 0, new Qty(5), 1, []
        );

        $this->assertSame(EstadoPlanificado::PROGRAMADO, $produccionBatch->estado);

        $events = $produccionBatch->pullEvents();
        $this->assertCount(1, $events);
        $this->assertSame(ProduccionBatchCreado::class, $events[0]->name());

        $payload = $events[0]->toArray();
        $this->assertSame('123', $payload['ordenProduccionId']);
        $this->assertSame(2, $payload['estacionId']);
        $this->assertSame('10', $payload['productoId']);
        $this->assertSame('7', $payload['recetaVersionId']);
        $this->assertSame('3', $payload['porcionId']);
        $this->assertSame(5, $payload['qty']);
        $this->assertSame(1, $payload['posicion']);
    }
}

<?php

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\ProduccionBatchCreado;
use App\Domain\Produccion\Aggregate\ProduccionBatch;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\ValueObjects\Qty;
use PHPUnit\Framework\TestCase;

class ProduccionBatchCrearTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_crear_records_event_and_sets_initial_state(): void
    {
        $b = ProduccionBatch::crear(
            id: 1,
            ordenProduccionId: 123,
            productoId: 10,
            estacionId: 2,
            recetaVersionId: 7,
            porcionId: 3,
            cantPlanificada: 5,
            cantProducida: 0,
            mermaGr: 0,
            estado: EstadoPlanificado::PROGRAMADO,
            rendimiento: 0,
            qty: new Qty(5),
            posicion: 1,
            ruta: []
        );

        $this->assertSame(EstadoPlanificado::PROGRAMADO, $b->estado);

        $events = $b->pullEvents();
        $this->assertCount(1, $events);
        $this->assertSame(ProduccionBatchCreado::class, $events[0]->name());

        $payload = $events[0]->toArray();
        $this->assertSame(1, $payload['batch_id']);
        $this->assertSame('123', $payload['ordenProduccionId']);
        $this->assertSame(2, $payload['estacionId']);
        $this->assertSame(5, $payload['qty']);
        $this->assertSame(1, $payload['posicion']);
    }
}
<?php

namespace Tests\Unit\Domain\Produccion\Events;

use App\Domain\Produccion\Events\OrdenProduccionPlanificada;
use App\Domain\Produccion\Events\OrdenProduccionProcesada;
use App\Domain\Produccion\Events\OrdenProduccionCerrada;
use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Produccion\Events\ProduccionBatchCreado;
use App\Domain\Produccion\ValueObjects\Qty;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class DomainEventsTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_orden_produccion_creada_to_array_contains_expected_fields(): void
    {
        $fecha = new DateTimeImmutable('2025-11-04');
        $e = new OrdenProduccionCreada(123, $fecha, 'SCZ-001');

        $this->assertSame(OrdenProduccionCreada::class, $e->name());
        $this->assertSame(123, $e->aggregateId());

        $payload = $e->toArray();
        $this->assertSame(123, $payload['op_id']);
        $this->assertSame($fecha, $payload['fecha']);
        $this->assertSame('SCZ-001', $payload['sucursalId']);
    }

    /**
     * @inheritDoc
     */
    public function test_state_change_events_payload_shape(): void
    {
        $fecha = new DateTimeImmutable('2025-11-04');

        $p = new OrdenProduccionPlanificada(1, $fecha);
        $this->assertSame(1, $p->toArray()['op_id']);

        $pr = new OrdenProduccionProcesada(1, $fecha);
        $this->assertSame(1, $pr->toArray()['op_id']);

        $c = new OrdenProduccionCerrada(1, $fecha);
        $this->assertSame(1, $c->toArray()['op_id']);
    }

    /**
     * @inheritDoc
     */
    public function test_produccion_batch_creado_to_array_contains_expected_fields(): void
    {
        $e = new ProduccionBatchCreado(10, 123, 7, new Qty(2), 1);

        $payload = $e->toArray();
        $this->assertSame(10, $payload['batch_id']);
        $this->assertSame('123', $payload['ordenProduccionId']);
        $this->assertSame(7, $payload['estacionId']);
        $this->assertSame(2, $payload['qty']);
        $this->assertSame(1, $payload['posicion']);
    }
}

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
     * @return void
     */
    public function test_orden_produccion_creada_to_array_contains_expected_fields(): void
    {
        $fecha = new DateTimeImmutable('2025-11-04');
        $ordenProduccionCreada = new OrdenProduccionCreada(123, $fecha, 'SCZ-001');

        $this->assertSame(OrdenProduccionCreada::class, $ordenProduccionCreada->name());
        $this->assertSame(123, $ordenProduccionCreada->aggregateId());

        $payload = $ordenProduccionCreada->toArray();
        $this->assertSame(123, $payload['op_id']);
        $this->assertSame($fecha, $payload['fecha']);
        $this->assertSame('SCZ-001', $payload['sucursalId']);
    }

    /**
     * @return void
     */
    public function test_state_change_events_payload_shape(): void
    {
        $fecha = new DateTimeImmutable('2025-11-04');

        $ordenProduccionPlanificada = new OrdenProduccionPlanificada(1, $fecha);
        $this->assertSame(1, $ordenProduccionPlanificada->toArray()['op_id']);

        $ordenProduccionProcesada = new OrdenProduccionProcesada(1, $fecha);
        $this->assertSame(1, $ordenProduccionProcesada->toArray()['op_id']);

        $ordenProduccionCerrada = new OrdenProduccionCerrada(1, $fecha);
        $this->assertSame(1, $ordenProduccionCerrada->toArray()['op_id']);
    }

    /**
     * @return void
     */
    public function test_produccion_batch_creado_to_array_contains_expected_fields(): void
    {
        $productionBatchCreado = new ProduccionBatchCreado(10, 123, 7, new Qty(2), 1);

        $payload = $productionBatchCreado->toArray();
        $this->assertSame(10, $payload['batch_id']);
        $this->assertSame('123', $payload['ordenProduccionId']);
        $this->assertSame(7, $payload['estacionId']);
        $this->assertSame(2, $payload['qty']);
        $this->assertSame(1, $payload['posicion']);
    }
}
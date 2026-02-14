<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Events;

use App\Domain\Produccion\Events\OrdenProduccionPlanificada;
use App\Domain\Produccion\Events\OrdenProduccionProcesada;
use App\Domain\Produccion\Events\OrdenProduccionCerrada;
use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Produccion\Events\PaqueteParaDespachoCreado;
use App\Domain\Produccion\Events\ProduccionBatchCreado;
use App\Domain\Produccion\ValueObjects\Qty;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

/**
 * @class DomainEventsTest
 * @package Tests\Unit\Domain\Produccion\Events
 */
class DomainEventsTest extends TestCase
{
    /**
     * @return void
     */
    public function test_orden_produccion_creada_to_array_contains_expected_fields(): void
    {
        $fecha = new DateTimeImmutable('2025-11-04');
        $ordenProduccionCreada = new OrdenProduccionCreada(123, $fecha);

        $this->assertSame(OrdenProduccionCreada::class, $ordenProduccionCreada->name());
        $this->assertSame(123, $ordenProduccionCreada->aggregateId());

        $payload = $ordenProduccionCreada->toArray();
        $this->assertSame($fecha->format(DATE_ATOM), $payload['fecha']);
    }

    /**
     * @return void
     */
    public function test_state_change_events_payload_shape(): void
    {
        $fecha = new DateTimeImmutable('2025-11-04');

        $ordenProduccionPlanificada = new OrdenProduccionPlanificada(1, $fecha);
        $this->assertSame($fecha->format(DATE_ATOM), $ordenProduccionPlanificada->toArray()['fecha']);

        $ordenProduccionProcesada = new OrdenProduccionProcesada(1, $fecha);
        $this->assertSame($fecha->format(DATE_ATOM), $ordenProduccionProcesada->toArray()['fecha']);

        $ordenProduccionCerrada = new OrdenProduccionCerrada(1, $fecha);
        $this->assertSame($fecha->format(DATE_ATOM), $ordenProduccionCerrada->toArray()['fecha']);
    }

    /**
     * @return void
     */
    public function test_produccion_batch_creado_to_array_contains_expected_fields(): void
    {
        $productionBatchCreado = new ProduccionBatchCreado(10, 123, 7, 99, 9, 11, new Qty(2), 1);

        $payload = $productionBatchCreado->toArray();
        $this->assertSame('123', $payload['ordenProduccionId']);
        $this->assertSame(7, $payload['estacionId']);
        $this->assertSame('99', $payload['productoId']);
        $this->assertSame('9', $payload['recetaVersionId']);
        $this->assertSame('11', $payload['porcionId']);
        $this->assertSame(2, $payload['qty']);
        $this->assertSame(1, $payload['posicion']);
    }

    /**
     * @return void
     */
    public function test_paquete_para_despacho_creado_to_array_contains_new_contract_fields(): void
    {
        $event = new PaqueteParaDespachoCreado(
            '69be1d37-c26d-4a0e-be0e-f0b3e532d6ff',
            'PKG-69BE1D37C26D',
            '6f86f687-b30d-4292-a566-7dc31ef2677a',
            'Juan Perez',
            'Av. Banzer 123, SCZ',
            -17.7833,
            -63.1821,
            '2026-02-14'
        );

        $payload = $event->toArray();
        $this->assertSame('69be1d37-c26d-4a0e-be0e-f0b3e532d6ff', $payload['id']);
        $this->assertSame('PKG-69BE1D37C26D', $payload['number']);
        $this->assertSame('6f86f687-b30d-4292-a566-7dc31ef2677a', $payload['patientId']);
        $this->assertSame('Juan Perez', $payload['patientName']);
        $this->assertSame('Av. Banzer 123, SCZ', $payload['deliveryAddress']);
        $this->assertSame(-17.7833, $payload['deliveryLatitude']);
        $this->assertSame(-63.1821, $payload['deliveryLongitude']);
        $this->assertSame('2026-02-14', $payload['deliveryDate']);
    }
}

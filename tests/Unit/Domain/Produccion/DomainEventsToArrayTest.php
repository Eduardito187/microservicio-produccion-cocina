<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\Events\CalendarioActualizado;
use App\Domain\Produccion\Events\CalendarioCreado;
use App\Domain\Produccion\Events\CalendarioItemActualizado;
use App\Domain\Produccion\Events\CalendarioItemCreado;
use App\Domain\Produccion\Events\DireccionActualizada;
use App\Domain\Produccion\Events\DireccionCreada;
use App\Domain\Produccion\Events\EntregaInconsistenciaDetectada;
use App\Domain\Produccion\Events\OrdenEntregaCompletada;
use App\Domain\Produccion\Events\OrdenProduccionCerrada;
use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Produccion\Events\OrdenProduccionDespachada;
use App\Domain\Produccion\Events\OrdenProduccionPlanificada;
use App\Domain\Produccion\Events\OrdenProduccionProcesada;
use App\Domain\Produccion\Events\PacienteActualizado;
use App\Domain\Produccion\Events\PacienteCreado;
use App\Domain\Produccion\Events\PaqueteActualizado;
use App\Domain\Produccion\Events\PaqueteCreado;
use App\Domain\Produccion\Events\PaqueteEntregado;
use App\Domain\Produccion\Events\PaqueteParaDespachoCreado;
use App\Domain\Produccion\Events\ProduccionBatchCreado;
use App\Domain\Produccion\Events\ProductoActualizado;
use App\Domain\Produccion\Events\ProductoCreado;
use App\Domain\Produccion\Events\RecetaActualizada;
use App\Domain\Produccion\Events\RecetaCreada;
use App\Domain\Produccion\Events\RecetaVersionActualizada;
use App\Domain\Produccion\Events\RecetaVersionCreada;
use App\Domain\Produccion\Events\SuscripcionActualizada;
use App\Domain\Produccion\Events\SuscripcionCreada;
use App\Domain\Produccion\ValueObjects\Qty;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @class DomainEventsToArrayTest
 */
class DomainEventsToArrayTest extends TestCase
{
    // ─── Calendario ────────────────────────────────────────────────────────

    public function test_calendario_actualizado_to_array(): void
    {
        $event = new CalendarioActualizado('cal-1', new DateTimeImmutable('2026-01-15T08:00:00Z'));
        $arr = $event->toArray();
        $this->assertArrayHasKey('fecha', $arr);
        $this->assertStringContainsString('2026-01-15', $arr['fecha']);
    }

    public function test_calendario_creado_to_array(): void
    {
        $event = new CalendarioCreado('cal-2', new DateTimeImmutable('2026-02-20T09:00:00Z'));
        $arr = $event->toArray();
        $this->assertArrayHasKey('fecha', $arr);
        $this->assertStringContainsString('2026-02-20', $arr['fecha']);
    }

    // ─── CalendarioItem ────────────────────────────────────────────────────

    public function test_calendario_item_actualizado_to_array(): void
    {
        $event = new CalendarioItemActualizado('item-1', 'cal-10', 'desp-10');
        $arr = $event->toArray();
        $this->assertSame('cal-10', $arr['calendarioId']);
        $this->assertSame('desp-10', $arr['itemDespachoId']);
    }

    public function test_calendario_item_creado_to_array(): void
    {
        $event = new CalendarioItemCreado('item-2', 'cal-20', 'desp-20');
        $arr = $event->toArray();
        $this->assertSame('cal-20', $arr['calendarioId']);
        $this->assertSame('desp-20', $arr['itemDespachoId']);
    }

    // ─── Direccion ─────────────────────────────────────────────────────────

    public function test_direccion_creada_to_array(): void
    {
        $event = new DireccionCreada('dir-1', 'Casa', 'Calle 10', 'Apt 2', 'Bogota', 'Cundinamarca', 'CO', [1.0, 2.0]);
        $arr = $event->toArray();
        $this->assertSame('Casa', $arr['nombre']);
        $this->assertSame('Calle 10', $arr['linea1']);
        $this->assertSame('Apt 2', $arr['linea2']);
        $this->assertSame('Bogota', $arr['ciudad']);
        $this->assertSame('Cundinamarca', $arr['provincia']);
        $this->assertSame('CO', $arr['pais']);
        $this->assertSame([1.0, 2.0], $arr['geo']);
    }

    public function test_direccion_actualizada_to_array(): void
    {
        $event = new DireccionActualizada('dir-2', null, 'Carrera 5', null, 'Medellin', null, 'CO', null);
        $arr = $event->toArray();
        $this->assertNull($arr['nombre']);
        $this->assertSame('Carrera 5', $arr['linea1']);
        $this->assertNull($arr['geo']);
    }

    // ─── EntregaInconsistenciaDetectada ────────────────────────────────────

    public function test_entrega_inconsistencia_detectada_to_array(): void
    {
        $event = new EntregaInconsistenciaDetectada('op-1', 'paquete ya confirmado', 'evt-01', 'pkg-01', ['foo' => 'bar']);
        $arr = $event->toArray();
        $this->assertSame('op-1', $arr['ordenProduccionId']);
        $this->assertSame('evt-01', $arr['eventId']);
        $this->assertSame('pkg-01', $arr['packageId']);
        $this->assertSame('paquete ya confirmado', $arr['reason']);
        $this->assertSame(['foo' => 'bar'], $arr['payload']);
    }

    public function test_entrega_inconsistencia_detectada_sin_event_id(): void
    {
        $event = new EntregaInconsistenciaDetectada('op-2', 'sin event', null, null);
        $arr = $event->toArray();
        $this->assertNull($arr['eventId']);
        $this->assertNull($arr['packageId']);
        $this->assertSame([], $arr['payload']);
    }

    // ─── OrdenEntregaCompletada ────────────────────────────────────────────

    public function test_orden_entrega_completada_to_array(): void
    {
        $event = new OrdenEntregaCompletada(
            'op-3',
            'entrega-3',
            'con-3',
            10, 9, 1,
            new DateTimeImmutable('2026-03-01T12:00:00Z')
        );
        $arr = $event->toArray();
        $this->assertSame('op-3', $arr['ordenProduccionId']);
        $this->assertSame(10, $arr['totalPackages']);
        $this->assertSame(9, $arr['confirmedPackages']);
        $this->assertSame(1, $arr['failedPackages']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $arr['completedAt']);
    }

    // ─── OrdenProduccion events ────────────────────────────────────────────

    public function test_orden_produccion_cerrada_to_array(): void
    {
        $event = new OrdenProduccionCerrada('op-4', new DateTimeImmutable('2026-04-01'));
        $arr = $event->toArray();
        $this->assertArrayHasKey('fecha', $arr);
        $this->assertStringContainsString('2026-04-01', $arr['fecha']);
    }

    public function test_orden_produccion_creada_to_array(): void
    {
        $event = new OrdenProduccionCreada('op-5', new DateTimeImmutable('2026-05-01T10:00:00Z'), 'CREADA', 3, 2, 5);
        $arr = $event->toArray();
        $this->assertSame('op-5', $arr['ordenProduccionId']);
        $this->assertSame('CREADA', $arr['estado']);
        $this->assertSame(3, $arr['itemsCount']);
        $this->assertSame(2, $arr['batchesCount']);
        $this->assertSame(5, $arr['itemsDespachoCount']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $arr['fecha']);
    }

    public function test_orden_produccion_despachada_to_array(): void
    {
        $event = new OrdenProduccionDespachada('op-6', new DateTimeImmutable('2026-06-01'), 7);
        $arr = $event->toArray();
        $this->assertArrayHasKey('fecha', $arr);
        $this->assertSame(7, $arr['itemsDespachoCount']);
    }

    public function test_orden_produccion_planificada_to_array(): void
    {
        $event = new OrdenProduccionPlanificada('op-7', new DateTimeImmutable('2026-07-01T09:00:00Z'), 'CREADA', 'PLANIFICADA', 4, 3, 8);
        $arr = $event->toArray();
        $this->assertSame('op-7', $arr['ordenProduccionId']);
        $this->assertSame('CREADA', $arr['estadoAnterior']);
        $this->assertSame('PLANIFICADA', $arr['estadoActual']);
        $this->assertSame(4, $arr['itemsCount']);
        $this->assertSame(3, $arr['batchesCount']);
        $this->assertSame(8, $arr['itemsDespachoCount']);
    }

    public function test_orden_produccion_procesada_to_array(): void
    {
        $event = new OrdenProduccionProcesada('op-8', new DateTimeImmutable('2026-08-01'));
        $arr = $event->toArray();
        $this->assertArrayHasKey('fecha', $arr);
        $this->assertStringContainsString('2026-08-01', $arr['fecha']);
    }

    // ─── PacienteCreado / PacienteActualizado ──────────────────────────────

    public function test_paciente_creado_to_array(): void
    {
        $event = new PacienteCreado('pac-1', 'Maria Rojas', '987654321', 'sus-1');
        $arr = $event->toArray();
        $this->assertSame('Maria Rojas', $arr['nombre']);
        $this->assertSame('987654321', $arr['documento']);
        $this->assertSame('sus-1', $arr['suscripcionId']);
    }

    public function test_paciente_actualizado_to_array(): void
    {
        $event = new PacienteActualizado('pac-2', 'Carlos Vera', null, null);
        $arr = $event->toArray();
        $this->assertSame('Carlos Vera', $arr['nombre']);
        $this->assertNull($arr['documento']);
        $this->assertNull($arr['suscripcionId']);
    }

    // ─── Paquete events ────────────────────────────────────────────────────

    public function test_paquete_creado_to_array(): void
    {
        $event = new PaqueteCreado('paq-1', 'etq-1', 'ven-1', 'dir-1');
        $arr = $event->toArray();
        $this->assertSame('etq-1', $arr['etiquetaId']);
        $this->assertSame('ven-1', $arr['ventanaId']);
        $this->assertSame('dir-1', $arr['direccionId']);
    }

    public function test_paquete_actualizado_to_array(): void
    {
        $event = new PaqueteActualizado('paq-2', null, 'ven-2', 'dir-2');
        $arr = $event->toArray();
        $this->assertNull($arr['etiquetaId']);
        $this->assertSame('ven-2', $arr['ventanaId']);
        $this->assertSame('dir-2', $arr['direccionId']);
    }

    public function test_paquete_entregado_to_array(): void
    {
        $event = new PaqueteEntregado('op-9', 'cal-9', 'con-9', 'confirmada');
        $arr = $event->toArray();
        $this->assertSame('op-9', $arr['ordenProduccionId']);
        $this->assertSame('cal-9', $arr['calendarioId']);
        $this->assertSame('con-9', $arr['contratoId']);
        $this->assertSame('confirmada', $arr['estado']);
    }

    public function test_paquete_para_despacho_creado_to_array(): void
    {
        $event = new PaqueteParaDespachoCreado(
            'paq-3',
            'PKT-001',
            'pac-3',
            'Jorge Lemos',
            'Av Principal 100',
            4.6097,
            -74.0817,
            '2026-09-15'
        );
        $arr = $event->toArray();
        $this->assertSame('paq-3', $arr['id']);
        $this->assertSame('PKT-001', $arr['number']);
        $this->assertSame('pac-3', $arr['patientId']);
        $this->assertSame('Jorge Lemos', $arr['patientName']);
        $this->assertSame('Av Principal 100', $arr['deliveryAddress']);
        $this->assertSame(4.6097, $arr['deliveryLatitude']);
        $this->assertSame(-74.0817, $arr['deliveryLongitude']);
        $this->assertSame('2026-09-15', $arr['deliveryDate']);
    }

    // ─── ProduccionBatchCreado ─────────────────────────────────────────────

    public function test_produccion_batch_creado_to_array(): void
    {
        $event = new ProduccionBatchCreado('bat-1', 'op-10', 'prod-1', 'por-1', new Qty(5), 2);
        $arr = $event->toArray();
        $this->assertSame('op-10', $arr['ordenProduccionId']);
        $this->assertSame('prod-1', $arr['productoId']);
        $this->assertSame('por-1', $arr['porcionId']);
        $this->assertSame(5, $arr['qty']);
        $this->assertSame(2, $arr['posicion']);
    }

    public function test_produccion_batch_creado_con_ids_null(): void
    {
        $event = new ProduccionBatchCreado(null, null, null, null, new Qty(1), 0);
        $arr = $event->toArray();
        $this->assertNull($arr['ordenProduccionId']);
        $this->assertNull($arr['productoId']);
        $this->assertNull($arr['porcionId']);
    }

    // ─── Producto events ───────────────────────────────────────────────────

    public function test_producto_creado_to_array(): void
    {
        $event = new ProductoCreado('pro-1', 'SKU-001', 29.99, 24.99);
        $arr = $event->toArray();
        $this->assertSame('SKU-001', $arr['sku']);
        $this->assertSame(29.99, $arr['price']);
        $this->assertSame(24.99, $arr['specialPrice']);
    }

    public function test_producto_actualizado_to_array(): void
    {
        $event = new ProductoActualizado('pro-2', 'SKU-002', 39.99, 34.99);
        $arr = $event->toArray();
        $this->assertSame('SKU-002', $arr['sku']);
        $this->assertSame(39.99, $arr['price']);
        $this->assertSame(34.99, $arr['specialPrice']);
    }

    // ─── RecetaVersion events ──────────────────────────────────────────────

    public function test_receta_version_creada_to_array(): void
    {
        $event = new RecetaVersionCreada(
            'rec-1', 'Dieta Proteica',
            ['kcal' => 500, 'proteina' => 40],
            [['item' => 'Pollo', 'gr' => 200]],
            'Alta proteina',
            'Mezclar y servir',
            500
        );
        $arr = $event->toArray();
        $this->assertSame('Dieta Proteica', $arr['nombre']);
        $this->assertSame(['kcal' => 500, 'proteina' => 40], $arr['nutrientes']);
        $this->assertSame([['item' => 'Pollo', 'gr' => 200]], $arr['ingredientes']);
        $this->assertSame('Alta proteina', $arr['description']);
        $this->assertSame('Mezclar y servir', $arr['instructions']);
        $this->assertSame(500, $arr['totalCalories']);
    }

    public function test_receta_version_creada_con_fields_null(): void
    {
        $event = new RecetaVersionCreada('rec-2', 'Basica', null, null);
        $arr = $event->toArray();
        $this->assertSame('Basica', $arr['nombre']);
        $this->assertNull($arr['nutrientes']);
        $this->assertNull($arr['ingredientes']);
        $this->assertNull($arr['description']);
        $this->assertNull($arr['totalCalories']);
    }

    public function test_receta_version_actualizada_to_array(): void
    {
        $event = new RecetaVersionActualizada(
            'rec-3', 'Dieta Actualizada',
            ['kcal' => 600],
            [],
            null, null, null
        );
        $arr = $event->toArray();
        $this->assertSame('Dieta Actualizada', $arr['nombre']);
        $this->assertSame(['kcal' => 600], $arr['nutrientes']);
    }

    public function test_receta_creada_hereda_receta_version_creada(): void
    {
        $event = new RecetaCreada('rec-4', 'Receta heredada', null, null);
        $arr = $event->toArray();
        $this->assertSame('Receta heredada', $arr['nombre']);
    }

    public function test_receta_actualizada_hereda_receta_version_actualizada(): void
    {
        $event = new RecetaActualizada('rec-5', 'Receta act heredada', null, null);
        $arr = $event->toArray();
        $this->assertSame('Receta act heredada', $arr['nombre']);
    }

    // ─── Suscripcion events ────────────────────────────────────────────────

    public function test_suscripcion_creada_to_array(): void
    {
        $event = new SuscripcionCreada('sus-1', 'Plan Mensual');
        $arr = $event->toArray();
        $this->assertSame('Plan Mensual', $arr['nombre']);
    }

    public function test_suscripcion_actualizada_to_array(): void
    {
        $event = new SuscripcionActualizada('sus-2', 'Plan Trimestral');
        $arr = $event->toArray();
        $this->assertSame('Plan Trimestral', $arr['nombre']);
    }
}

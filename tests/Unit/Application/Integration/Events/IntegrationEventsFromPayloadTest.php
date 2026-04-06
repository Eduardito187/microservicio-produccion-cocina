<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration\Events;

use App\Application\Integration\Events\CalendarioEntregaCreadoEvent;
use App\Application\Integration\Events\DiaSinEntregaMarcadoEvent;
use App\Application\Integration\Events\DireccionActualizadaEvent;
use App\Application\Integration\Events\DireccionCreadaEvent;
use App\Application\Integration\Events\EntregaConfirmadaEvent;
use App\Application\Integration\Events\EntregaFallidaEvent;
use App\Application\Integration\Events\EntregaProgramadaEvent;
use App\Application\Integration\Events\PacienteActualizadoEvent;
use App\Application\Integration\Events\PacienteCreadoEvent;
use App\Application\Integration\Events\PaqueteEnRutaEvent;
use App\Application\Integration\Events\SuscripcionActualizadaEvent;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @class IntegrationEventsFromPayloadTest
 */
class IntegrationEventsFromPayloadTest extends TestCase
{
    // ─── PacienteCreadoEvent ───────────────────────────────────────────────

    public function test_paciente_creado_event_mapea_campos_canonicos(): void
    {
        $event = PacienteCreadoEvent::fromPayload([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000001',
            'nombre' => 'Juan Perez',
            'documento' => '12345678',
            'suscripcionId' => 'a1b2c3d4-0000-0000-0000-000000000002',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000001', $event->id);
        $this->assertSame('Juan Perez', $event->nombre);
        $this->assertSame('12345678', $event->documento);
        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000002', $event->suscripcionId);
    }

    public function test_paciente_creado_event_usa_alias_paciente_id(): void
    {
        $event = PacienteCreadoEvent::fromPayload([
            'pacienteId' => 'a1b2c3d4-0000-0000-0000-000000000003',
            'name' => 'Maria Lopez',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000003', $event->id);
        $this->assertSame('Maria Lopez', $event->nombre);
        $this->assertNull($event->documento);
        $this->assertNull($event->suscripcionId);
    }

    public function test_paciente_creado_event_lanza_excepcion_si_falta_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PacienteCreadoEvent::fromPayload(['nombre' => 'Sin ID']);
    }

    public function test_paciente_creado_event_lanza_excepcion_si_falta_nombre(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PacienteCreadoEvent::fromPayload(['id' => 'a1b2c3d4-0000-0000-0000-000000000001']);
    }

    // ─── PacienteActualizadoEvent ──────────────────────────────────────────

    public function test_paciente_actualizado_event_mapea_campos(): void
    {
        $event = PacienteActualizadoEvent::fromPayload([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000004',
            'nombre' => 'Pedro Garcia',
            'documento' => '98765432',
            'suscripcionId' => 'a1b2c3d4-0000-0000-0000-000000000005',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000004', $event->id);
        $this->assertSame('Pedro Garcia', $event->nombre);
        $this->assertSame('98765432', $event->documento);
        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000005', $event->suscripcionId);
    }

    public function test_paciente_actualizado_event_permite_campos_opcionales_nulos(): void
    {
        $event = PacienteActualizadoEvent::fromPayload([
            'pacienteId' => 'a1b2c3d4-0000-0000-0000-000000000006',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000006', $event->id);
        $this->assertNull($event->nombre);
        $this->assertNull($event->documento);
        $this->assertNull($event->suscripcionId);
    }

    // ─── DireccionCreadaEvent ──────────────────────────────────────────────

    public function test_direccion_creada_event_mapea_campos_canonicos(): void
    {
        $event = DireccionCreadaEvent::fromPayload([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000010',
            'nombre' => 'Casa',
            'linea1' => 'Calle Falsa 123',
            'linea2' => 'Piso 2',
            'ciudad' => 'Bogota',
            'provincia' => 'Cundinamarca',
            'pais' => 'CO',
            'geo' => ['lat' => 4.6, 'lon' => -74.1],
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000010', $event->id);
        $this->assertSame('Casa', $event->nombre);
        $this->assertSame('Calle Falsa 123', $event->linea1);
        $this->assertSame('Piso 2', $event->linea2);
        $this->assertSame('Bogota', $event->ciudad);
        $this->assertSame('CO', $event->pais);
        $this->assertIsArray($event->geo);
    }

    public function test_direccion_creada_event_usa_aliases(): void
    {
        $event = DireccionCreadaEvent::fromPayload([
            'direccionId' => 'a1b2c3d4-0000-0000-0000-000000000011',
            'line1' => 'Av Siempreviva 742',
            'city' => 'Springfield',
            'country' => 'US',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000011', $event->id);
        $this->assertSame('Av Siempreviva 742', $event->linea1);
        $this->assertSame('Springfield', $event->ciudad);
        $this->assertSame('US', $event->pais);
    }

    public function test_direccion_creada_event_lanza_excepcion_si_falta_linea1(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DireccionCreadaEvent::fromPayload([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000012',
        ]);
    }

    // ─── DireccionActualizadaEvent ─────────────────────────────────────────

    public function test_direccion_actualizada_event_mapea_campos(): void
    {
        $event = DireccionActualizadaEvent::fromPayload([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000013',
            'linea1' => 'Nueva Calle 456',
            'ciudad' => 'Medellin',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000013', $event->id);
        $this->assertSame('Nueva Calle 456', $event->linea1);
        $this->assertSame('Medellin', $event->ciudad);
        $this->assertNull($event->linea2);
        $this->assertNull($event->nombre);
    }

    public function test_direccion_actualizada_event_todos_los_campos(): void
    {
        $event = DireccionActualizadaEvent::fromPayload([
            'direccionId' => 'a1b2c3d4-0000-0000-0000-000000000014',
            'nombre' => 'Oficina',
            'linea1' => 'Carrera 15',
            'linea2' => 'Of 301',
            'ciudad' => 'Cali',
            'state' => 'Valle',
            'country' => 'CO',
            'geolocalizacion' => ['lat' => 3.4, 'lon' => -76.5],
        ]);

        $this->assertSame('Oficina', $event->nombre);
        $this->assertSame('Valle', $event->provincia);
        $this->assertIsArray($event->geo);
    }

    // ─── SuscripcionActualizadaEvent ───────────────────────────────────────

    public function test_suscripcion_actualizada_event_mapea_campos(): void
    {
        $event = SuscripcionActualizadaEvent::fromPayload([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000020',
            'nombre' => 'Plan Mensual',
            'pacienteId' => 'a1b2c3d4-0000-0000-0000-000000000021',
            'tipoServicio' => 'MENSUAL',
            'fechaInicio' => '2026-01-01',
            'fechaFin' => '2026-02-01',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000020', $event->id);
        $this->assertSame('Plan Mensual', $event->nombre);
        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000021', $event->pacienteId);
        $this->assertSame('MENSUAL', $event->tipoServicio);
        $this->assertSame('2026-01-01', $event->fechaInicio);
    }

    public function test_suscripcion_actualizada_event_acepta_alias_contrato_id(): void
    {
        $event = SuscripcionActualizadaEvent::fromPayload([
            'contratoId' => 'a1b2c3d4-0000-0000-0000-000000000022',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000022', $event->id);
        $this->assertNull($event->fechaFin);
    }

    // ─── CalendarioEntregaCreadoEvent ──────────────────────────────────────

    public function test_calendario_entrega_creado_event_mapea_campos_canonicos(): void
    {
        $event = CalendarioEntregaCreadoEvent::fromPayload([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000030',
            'fecha' => '2026-03-15',
            'hora' => '10:00',
            'entregaId' => 'a1b2c3d4-0000-0000-0000-000000000031',
            'contratoId' => 'a1b2c3d4-0000-0000-0000-000000000032',
            'estado' => 1,
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000030', $event->id);
        $this->assertSame('2026-03-15', $event->fecha);
        $this->assertSame('10:00', $event->hora);
        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000031', $event->entregaId);
        $this->assertSame(1, $event->estado);
    }

    public function test_calendario_entrega_creado_event_deriva_id_desde_entrega_id(): void
    {
        $event = CalendarioEntregaCreadoEvent::fromPayload([
            'entregaId' => 'a1b2c3d4-0000-0000-0000-000000000033',
            'fecha' => '2026-04-10',
        ]);

        $this->assertNotEmpty($event->id);
        $this->assertSame('2026-04-10', $event->fecha);
    }

    public function test_calendario_entrega_creado_event_deriva_fecha_de_occurred_on(): void
    {
        $event = CalendarioEntregaCreadoEvent::fromPayload([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000034',
            'occurredOn' => '2026-05-20T14:30:00Z',
        ]);

        $this->assertSame('2026-05-20', $event->fecha);
    }

    public function test_calendario_entrega_creado_event_lanza_si_falta_fecha_e_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CalendarioEntregaCreadoEvent::fromPayload([]);
    }

    // ─── EntregaConfirmadaEvent ────────────────────────────────────────────

    public function test_entrega_confirmada_event_mapea_campos(): void
    {
        $event = EntregaConfirmadaEvent::fromPayload([
            'paqueteId' => 'a1b2c3d4-0000-0000-0000-000000000040',
            'fotoUrl' => 'https://storage.example.com/foto.jpg',
            'geo' => ['lat' => 4.6, 'lon' => -74.1],
            'occurredOn' => '2026-03-01T12:00:00Z',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000040', $event->paqueteId);
        $this->assertSame('https://storage.example.com/foto.jpg', $event->fotoUrl);
        $this->assertIsArray($event->geo);
        $this->assertSame('2026-03-01T12:00:00Z', $event->occurredOn);
    }

    public function test_entrega_confirmada_event_acepta_alias_paquete_id(): void
    {
        $event = EntregaConfirmadaEvent::fromPayload([
            'paquete_id' => 'a1b2c3d4-0000-0000-0000-000000000041',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000041', $event->paqueteId);
        $this->assertNull($event->fotoUrl);
        $this->assertNull($event->geo);
    }

    public function test_entrega_confirmada_event_lanza_si_falta_paquete_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        EntregaConfirmadaEvent::fromPayload(['fotoUrl' => 'http://x.com/foto.jpg']);
    }

    // ─── EntregaFallidaEvent ───────────────────────────────────────────────

    public function test_entrega_fallida_event_mapea_campos(): void
    {
        $event = EntregaFallidaEvent::fromPayload([
            'paqueteId' => 'a1b2c3d4-0000-0000-0000-000000000050',
            'motivo' => 'Destinatario ausente',
            'occurredOn' => '2026-03-02T09:00:00Z',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000050', $event->paqueteId);
        $this->assertSame('Destinatario ausente', $event->motivo);
        $this->assertSame('2026-03-02T09:00:00Z', $event->occurredOn);
    }

    public function test_entrega_fallida_event_acepta_alias_reason(): void
    {
        $event = EntregaFallidaEvent::fromPayload([
            'paquete_id' => 'a1b2c3d4-0000-0000-0000-000000000051',
            'reason' => 'Dirección incorrecta',
        ]);

        $this->assertSame('Dirección incorrecta', $event->motivo);
    }

    // ─── PaqueteEnRutaEvent ────────────────────────────────────────────────

    public function test_paquete_en_ruta_event_mapea_campos(): void
    {
        $event = PaqueteEnRutaEvent::fromPayload([
            'paqueteId' => 'a1b2c3d4-0000-0000-0000-000000000060',
            'rutaId' => 'ruta-001',
            'occurredOn' => '2026-03-03T08:00:00Z',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000060', $event->paqueteId);
        $this->assertSame('ruta-001', $event->rutaId);
        $this->assertSame('2026-03-03T08:00:00Z', $event->occurredOn);
    }

    public function test_paquete_en_ruta_event_permite_opcionales_nulos(): void
    {
        $event = PaqueteEnRutaEvent::fromPayload([
            'paquete_id' => 'a1b2c3d4-0000-0000-0000-000000000061',
        ]);

        $this->assertNull($event->rutaId);
        $this->assertNull($event->occurredOn);
    }

    // ─── EntregaProgramadaEvent ────────────────────────────────────────────

    public function test_entrega_programada_event_mapea_campos(): void
    {
        $event = EntregaProgramadaEvent::fromPayload([
            'calendarioId' => 'a1b2c3d4-0000-0000-0000-000000000070',
            'itemDespachoId' => 'a1b2c3d4-0000-0000-0000-000000000071',
            'ordenProduccionId' => 'a1b2c3d4-0000-0000-0000-000000000072',
            'pacienteId' => 'a1b2c3d4-0000-0000-0000-000000000073',
            'direccionId' => 'a1b2c3d4-0000-0000-0000-000000000074',
            'ventanaEntregaId' => 'a1b2c3d4-0000-0000-0000-000000000075',
            'items' => [['sku' => 'SKU-1', 'qty' => 2]],
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000070', $event->calendarioId);
        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000071', $event->itemDespachoId);
        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000072', $event->ordenProduccionId);
        $this->assertIsArray($event->items);
    }

    public function test_entrega_programada_event_acepta_aliases(): void
    {
        $event = EntregaProgramadaEvent::fromPayload([
            'calendario_id' => 'a1b2c3d4-0000-0000-0000-000000000076',
            'item_despacho_id' => 'a1b2c3d4-0000-0000-0000-000000000077',
            'op_id' => 'a1b2c3d4-0000-0000-0000-000000000078',
            'items_despacho' => [['productId' => 'p1', 'qty' => 1]],
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000076', $event->calendarioId);
        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000078', $event->ordenProduccionId);
        $this->assertIsArray($event->itemsDespacho);
    }

    public function test_entrega_programada_event_lanza_si_falta_calendario_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        EntregaProgramadaEvent::fromPayload([
            'itemDespachoId' => 'a1b2c3d4-0000-0000-0000-000000000079',
        ]);
    }

    // ─── DiaSinEntregaMarcadoEvent ─────────────────────────────────────────

    public function test_dia_sin_entrega_marcado_event_mapea_campos(): void
    {
        $event = DiaSinEntregaMarcadoEvent::fromPayload([
            'calendarioId' => 'a1b2c3d4-0000-0000-0000-000000000080',
            'fecha' => '2026-04-15',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000080', $event->calendarioId);
        $this->assertSame('2026-04-15', $event->fecha);
    }

    public function test_dia_sin_entrega_marcado_event_acepta_alias_date(): void
    {
        $event = DiaSinEntregaMarcadoEvent::fromPayload([
            'calendario_id' => 'a1b2c3d4-0000-0000-0000-000000000081',
            'date' => '2026-05-10',
        ]);

        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000081', $event->calendarioId);
        $this->assertSame('2026-05-10', $event->fecha);
    }

    public function test_dia_sin_entrega_marcado_event_permite_fecha_nula(): void
    {
        $event = DiaSinEntregaMarcadoEvent::fromPayload([
            'calendarioId' => 'a1b2c3d4-0000-0000-0000-000000000082',
        ]);

        $this->assertNull($event->fecha);
    }
}

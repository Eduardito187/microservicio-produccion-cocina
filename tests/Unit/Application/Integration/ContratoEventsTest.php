<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration;

use App\Application\Integration\Events\ContratoCanceladoEvent;
use App\Application\Integration\Events\SuscripcionCreadaEvent;
use PHPUnit\Framework\TestCase;

/**
 * @class ContratoEventsTest
 * @package Tests\Unit\Application\Integration
 */
class ContratoEventsTest extends TestCase
{
    /**
     * @return void
     */
    public function test_suscripcion_creada_event_parsea_payload_contrato_creado(): void
    {
        $event = SuscripcionCreadaEvent::fromPayload([
            'contratoId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
            'pacienteId' => 'a9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2c',
            'tipoServicio' => 'Plan 30 dias',
            'fechaInicio' => '2026-02-01',
            'fechaFin' => '2026-03-02',
        ]);

        $this->assertSame('d9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b', $event->id);
        $this->assertSame('a9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2c', $event->pacienteId);
        $this->assertSame('Plan 30 dias', $event->tipoServicio);
        $this->assertSame('2026-02-01', $event->fechaInicio);
        $this->assertSame('2026-03-02', $event->fechaFin);
    }

    /**
     * @return void
     */
    public function test_contrato_cancelado_event_parsea_payload(): void
    {
        $event = ContratoCanceladoEvent::fromPayload([
            'contratoId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
            'motivoCancelacion' => 'solicitud del paciente',
        ]);

        $this->assertSame('d9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b', $event->contratoId);
        $this->assertSame('solicitud del paciente', $event->motivoCancelacion);
    }
}

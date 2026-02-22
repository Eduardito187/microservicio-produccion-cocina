<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration;

use App\Application\Integration\Events\PacienteEliminadoEvent;
use PHPUnit\Framework\TestCase;

/**
 * @class PacienteEliminadoEventTest
 * @package Tests\Unit\Application\Integration
 */
class PacienteEliminadoEventTest extends TestCase
{
    /**
     * @return void
     */
    public function test_from_payload_parsea_paciente_id(): void
    {
        $event = PacienteEliminadoEvent::fromPayload([
            'pacienteId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
        ]);

        $this->assertSame('d9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b', $event->id);
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration;

use App\Application\Integration\CalendarProcessManager;
use App\Application\Integration\RecalculoProduccionService;
use PHPUnit\Framework\TestCase;

/**
 * @class CalendarProcessManagerTest
 */
class CalendarProcessManagerTest extends TestCase
{
    public function test_on_entrega_programada_llama_generar_op_y_despachar_op(): void
    {
        $service = $this->createMock(RecalculoProduccionService::class);

        $payload = ['calendarioId' => 'cal-01', 'itemDespachoId' => 'item-01'];

        $service->expects($this->once())->method('tryGenerarOP')->with($payload);
        $service->expects($this->once())->method('tryDespacharOP')->with($payload);

        $manager = new CalendarProcessManager($service);
        $manager->onEntregaProgramada($payload);
    }

    public function test_on_dia_sin_entrega_marcado_solo_llama_generar_op(): void
    {
        $service = $this->createMock(RecalculoProduccionService::class);

        $payload = ['calendarioId' => 'cal-02'];

        $service->expects($this->once())->method('tryGenerarOP')->with($payload);
        $service->expects($this->never())->method('tryDespacharOP');

        $manager = new CalendarProcessManager($service);
        $manager->onDiaSinEntregaMarcado($payload);
    }

    public function test_on_direccion_entrega_cambiada_solo_llama_despachar_op(): void
    {
        $service = $this->createMock(RecalculoProduccionService::class);

        $payload = ['pacienteId' => 'pac-01'];

        $service->expects($this->never())->method('tryGenerarOP');
        $service->expects($this->once())->method('tryDespacharOP')->with($payload);

        $manager = new CalendarProcessManager($service);
        $manager->onDireccionEntregaCambiada($payload);
    }

    public function test_on_entrega_programada_con_payload_vacio_no_lanza_excepcion(): void
    {
        $service = $this->createMock(RecalculoProduccionService::class);
        $service->expects($this->once())->method('tryGenerarOP');
        $service->expects($this->once())->method('tryDespacharOP');

        $manager = new CalendarProcessManager($service);
        $manager->onEntregaProgramada([]);
    }
}

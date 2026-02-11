<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration;

use App\Application\Integration\RecalculoProduccionService;
use App\Application\Produccion\Handler\GenerarOPHandler;
use App\Application\Produccion\Handler\DespachadorOPHandler;
use PHPUnit\Framework\TestCase;

/**
 * @class RecalculoProduccionServiceTest
 * @package Tests\Unit\Application\Integration
 */
class RecalculoProduccionServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function test_try_generar_op_valida_payload(): void
    {
        $generar = $this->createMock(GenerarOPHandler::class);
        $despachar = $this->createMock(DespachadorOPHandler::class);
        $service = new RecalculoProduccionService($generar, $despachar);

        $generar->expects($this->never())->method('__invoke');

        $this->assertFalse($service->tryGenerarOP(['fecha' => '2025-10-10']));
    }

    /**
     * @return void
     */
    public function test_try_generar_op_ejecuta_handler(): void
    {
        $generar = $this->createMock(GenerarOPHandler::class);
        $despachar = $this->createMock(DespachadorOPHandler::class);
        $service = new RecalculoProduccionService($generar, $despachar);

        $generar->expects($this->once())->method('__invoke');

        $payload = [
            'fecha' => '2025-10-10',
            'sucursalId' => 'SCZ-001',
            'items' => [
                ['sku' => 'P1', 'qty' => 1],
            ],
        ];

        $this->assertTrue($service->tryGenerarOP($payload));
    }

    /**
     * @return void
     */
    public function test_try_despachar_op_valida_payload(): void
    {
        $generar = $this->createMock(GenerarOPHandler::class);
        $despachar = $this->createMock(DespachadorOPHandler::class);
        $service = new RecalculoProduccionService($generar, $despachar);

        $despachar->expects($this->never())->method('__invoke');

        $this->assertFalse($service->tryDespacharOP(['ordenProduccionId' => 'x']));
    }

    /**
     * @return void
     */
    public function test_try_despachar_op_ejecuta_handler(): void
    {
        $generar = $this->createMock(GenerarOPHandler::class);
        $despachar = $this->createMock(DespachadorOPHandler::class);
        $service = new RecalculoProduccionService($generar, $despachar);

        $despachar->expects($this->once())->method('__invoke');

        $payload = [
            'ordenProduccionId' => 'op-1',
            'itemsDespacho' => [
                ['productId' => 'p1', 'qty' => 1],
            ],
        ];

        $this->assertTrue($service->tryDespacharOP($payload));
    }
}

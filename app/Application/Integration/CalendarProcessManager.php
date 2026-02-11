<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration;

/**
 * @class CalendarProcessManager
 * @package App\Application\Integration
 */
class CalendarProcessManager
{
    /**
     * @var RecalculoProduccionService
     */
    private $recalculoProduccionService;

    /**
     * Constructor
     *
     * @param RecalculoProduccionService $recalculoProduccionService
     */
    public function __construct(
        RecalculoProduccionService $recalculoProduccionService
    ) {
        $this->recalculoProduccionService = $recalculoProduccionService;
    }

    /**
     * @param array $payload
     * @return void
     */
    public function onEntregaProgramada(array $payload): void
    {
        $this->recalculoProduccionService->tryGenerarOP($payload);
        $this->recalculoProduccionService->tryDespacharOP($payload);
    }

    /**
     * @param array $payload
     * @return void
     */
    public function onDiaSinEntregaMarcado(array $payload): void
    {
        $this->recalculoProduccionService->tryGenerarOP($payload);
    }

    /**
     * @param array $payload
     * @return void
     */
    public function onDireccionEntregaCambiada(array $payload): void
    {
        $this->recalculoProduccionService->tryDespacharOP($payload);
    }
}

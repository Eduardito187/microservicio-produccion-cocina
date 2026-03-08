<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration;

/**
 * @class CalendarProcessManager
 */
class CalendarProcessManager
{
    /**
     * @var RecalculoProduccionService
     */
    private $recalculoProduccionService;

    /**
     * Constructor
     */
    public function __construct(
        RecalculoProduccionService $recalculoProduccionService
    ) {
        $this->recalculoProduccionService = $recalculoProduccionService;
    }

    public function onEntregaProgramada(array $payload): void
    {
        $this->recalculoProduccionService->tryGenerarOP($payload);
        $this->recalculoProduccionService->tryDespacharOP($payload);
    }

    public function onDiaSinEntregaMarcado(array $payload): void
    {
        $this->recalculoProduccionService->tryGenerarOP($payload);
    }

    public function onDireccionEntregaCambiada(array $payload): void
    {
        $this->recalculoProduccionService->tryDespacharOP($payload);
    }
}

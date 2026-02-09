<?php

namespace App\Application\Integration;

class CalendarProcessManager
{
    public function __construct(
        private readonly RecalculoProduccionService $recalculoProduccionService
    ) {
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

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarPaquetes;
use App\Application\Produccion\Repository\PaqueteQueryRepositoryInterface;

/**
 * @class ListarPaquetesHandler
 */
class ListarPaquetesHandler
{
    public function __construct(private PaqueteQueryRepositoryInterface $query) {}

    public function __invoke(ListarPaquetes $command): array
    {
        return $this->query->listConDetalles();
    }
}

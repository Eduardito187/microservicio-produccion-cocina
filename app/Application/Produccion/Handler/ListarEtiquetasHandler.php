<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarEtiquetas;
use App\Application\Produccion\Repository\EtiquetaQueryRepositoryInterface;

/**
 * @class ListarEtiquetasHandler
 */
class ListarEtiquetasHandler
{
    public function __construct(private EtiquetaQueryRepositoryInterface $query) {}

    public function __invoke(ListarEtiquetas $command): array
    {
        return $this->query->listConDetalles();
    }
}

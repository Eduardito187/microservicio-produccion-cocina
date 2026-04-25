<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarOrdenesProduccion;
use App\Application\Produccion\Repository\OrdenProduccionQueryRepositoryInterface;

/**
 * @class ListarOrdenesProduccionHandler
 */
class ListarOrdenesProduccionHandler
{
    /**
     * @var OrdenProduccionQueryRepositoryInterface
     */
    private $query;

    /**
     * Constructor
     */
    public function __construct(OrdenProduccionQueryRepositoryInterface $query)
    {
        $this->query = $query;
    }

    public function __invoke(ListarOrdenesProduccion $command): array
    {
        return $this->query->todos();
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarOrdenesPorSuscripcion;
use App\Application\Produccion\Repository\OrdenProduccionQueryRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class ListarOrdenesPorSuscripcionHandler
 */
class ListarOrdenesPorSuscripcionHandler
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

    public function __invoke(ListarOrdenesPorSuscripcion $command): array
    {
        $resultado = $this->query->consolidadoPorSuscripcion($command->suscripcionId);

        if ($resultado === null) {
            throw new EntityNotFoundException("La suscripcion id: {$command->suscripcionId} no existe.");
        }

        return $resultado;
    }
}

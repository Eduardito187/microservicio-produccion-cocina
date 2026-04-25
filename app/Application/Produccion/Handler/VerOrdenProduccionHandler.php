<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerOrdenProduccion;
use App\Application\Produccion\Repository\OrdenProduccionQueryRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class VerOrdenProduccionHandler
 */
class VerOrdenProduccionHandler
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

    public function __invoke(VerOrdenProduccion $command): array
    {
        $orden = $this->query->porId($command->id);

        if ($orden === null) {
            throw new EntityNotFoundException("La orden de produccion id: {$command->id} no existe.");
        }

        return $orden;
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerPaquete;
use App\Application\Produccion\Repository\PaqueteQueryRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class VerPaqueteHandler
 */
class VerPaqueteHandler
{
    public function __construct(private PaqueteQueryRepositoryInterface $query) {}

    public function __invoke(VerPaquete $command): array
    {
        $result = $this->query->porId($command->id);

        if ($result === null) {
            throw new EntityNotFoundException("El paquete id: {$command->id} no existe.");
        }

        return $result;
    }
}

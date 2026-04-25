<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerEtiqueta;
use App\Application\Produccion\Repository\EtiquetaQueryRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class VerEtiquetaHandler
 */
class VerEtiquetaHandler
{
    public function __construct(private EtiquetaQueryRepositoryInterface $query) {}

    public function __invoke(VerEtiqueta $command): array
    {
        $result = $this->query->porId($command->id);

        if ($result === null) {
            throw new EntityNotFoundException("La etiqueta id: {$command->id} no existe.");
        }

        return $result;
    }
}

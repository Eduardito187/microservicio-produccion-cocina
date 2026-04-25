<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Repository;

/**
 * @class PaqueteQueryRepositoryInterface
 */
interface PaqueteQueryRepositoryInterface
{
    public function listConDetalles(): array;

    public function porId(string $id): ?array;
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Repository;

/**
 * @class EtiquetaQueryRepositoryInterface
 */
interface EtiquetaQueryRepositoryInterface
{
    public function listConDetalles(): array;

    public function porId(string $id): ?array;
}

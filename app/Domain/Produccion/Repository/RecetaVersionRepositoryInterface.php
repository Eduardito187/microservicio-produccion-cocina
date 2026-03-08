<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\RecetaVersion;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class RecetaVersionRepositoryInterface
 */
interface RecetaVersionRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?RecetaVersion;

    /**
     * @return int
     */
    public function save(RecetaVersion $recetaVersion): string;

    /**
     * @return RecetaVersion[]
     */
    public function list(): array;

    public function delete(string|int $id): void;
}

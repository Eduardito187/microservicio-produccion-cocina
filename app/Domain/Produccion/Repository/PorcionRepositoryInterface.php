<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Porcion;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class PorcionRepositoryInterface
 */
interface PorcionRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Porcion;

    /**
     * @return int
     */
    public function save(Porcion $porcion): string;

    /**
     * @return Porcion[]
     */
    public function list(): array;

    public function delete(string|int $id): void;
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Direccion;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class DireccionRepositoryInterface
 */
interface DireccionRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Direccion;

    /**
     * @return int
     */
    public function save(Direccion $direccion): string;

    /**
     * @return Direccion[]
     */
    public function list(): array;

    public function delete(string|int $id): void;
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Paquete;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class PaqueteRepositoryInterface
 */
interface PaqueteRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Paquete;

    /**
     * @return int
     */
    public function save(Paquete $paquete): string;

    /**
     * @return Paquete[]
     */
    public function list(): array;

    public function delete(string|int $id): void;
}

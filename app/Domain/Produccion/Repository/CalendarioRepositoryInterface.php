<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class CalendarioRepositoryInterface
 */
interface CalendarioRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Calendario;

    /**
     * @return int
     */
    public function save(Calendario $calendario): string;

    /**
     * @return Calendario[]
     */
    public function list(): array;

    public function delete(string|int $id): void;
}

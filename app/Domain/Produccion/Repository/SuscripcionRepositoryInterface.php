<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class SuscripcionRepositoryInterface
 */
interface SuscripcionRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Suscripcion;

    /**
     * @return int
     */
    public function save(Suscripcion $suscripcion): string;

    /**
     * @return Suscripcion[]
     */
    public function list(): array;

    public function delete(string|int $id): void;
}

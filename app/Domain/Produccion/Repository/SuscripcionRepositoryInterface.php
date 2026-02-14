<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class SuscripcionRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface SuscripcionRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws EntityNotFoundException
     * @return Suscripcion|null
     */
    public function byId(string|int $id): ?Suscripcion;

    /**
     * @param Suscripcion $suscripcion
     * @return int
     */
    public function save(Suscripcion $suscripcion): string;

    /**
     * @return Suscripcion[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}

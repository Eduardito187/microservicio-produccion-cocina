<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Estacion;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class EstacionRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface EstacionRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws EntityNotFoundException
     * @return Estacion|null
     */
    public function byId(string|int $id): ?Estacion;

    /**
     * @param Estacion $estacion
     * @return int
     */
    public function save(Estacion $estacion): string;

    /**
     * @return Estacion[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}

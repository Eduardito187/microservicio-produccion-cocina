<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Direccion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class DireccionRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface DireccionRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws ModelNotFoundException
     * @return Direccion|null
     */
    public function byId(string|int $id): ?Direccion;

    /**
     * @param Direccion $direccion
     * @return int
     */
    public function save(Direccion $direccion): string;

    /**
     * @return Direccion[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}

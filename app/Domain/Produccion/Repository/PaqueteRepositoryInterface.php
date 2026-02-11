<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Paquete;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class PaqueteRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface PaqueteRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws ModelNotFoundException
     * @return Paquete|null
     */
    public function byId(string|int $id): ?Paquete;

    /**
     * @param Paquete $paquete
     * @return int
     */
    public function save(Paquete $paquete): string;

    /**
     * @return Paquete[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}

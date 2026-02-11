<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Porcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class PorcionRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface PorcionRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws ModelNotFoundException
     * @return Porcion|null
     */
    public function byId(string|int $id): ?Porcion;

    /**
     * @param Porcion $porcion
     * @return int
     */
    public function save(Porcion $porcion): string;

    /**
     * @return Porcion[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}

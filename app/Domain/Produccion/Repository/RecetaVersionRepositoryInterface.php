<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\RecetaVersion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class RecetaVersionRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface RecetaVersionRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws ModelNotFoundException
     * @return RecetaVersion|null
     */
    public function byId(string|int $id): ?RecetaVersion;

    /**
     * @param RecetaVersion $recetaVersion
     * @return int
     */
    public function save(RecetaVersion $recetaVersion): string;

    /**
     * @return RecetaVersion[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}

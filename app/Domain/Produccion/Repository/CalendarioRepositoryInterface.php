<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Calendario;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class CalendarioRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface CalendarioRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws ModelNotFoundException
     * @return Calendario|null
     */
    public function byId(string|int $id): ?Calendario;

    /**
     * @param Calendario $calendario
     * @return int
     */
    public function save(Calendario $calendario): string;

    /**
     * @return Calendario[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}

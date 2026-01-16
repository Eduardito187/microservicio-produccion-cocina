<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Estacion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface EstacionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Estacion|null
     */
    public function byId(int $id): ?Estacion;

    /**
     * @param Estacion $estacion
     * @return int
     */
    public function save(Estacion $estacion): int;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

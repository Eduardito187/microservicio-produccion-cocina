<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Direccion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface DireccionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Direccion|null
     */
    public function byId(int $id): ?Direccion;

    /**
     * @param Direccion $direccion
     * @return int
     */
    public function save(Direccion $direccion): int;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

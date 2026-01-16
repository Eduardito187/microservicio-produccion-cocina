<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Porcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface PorcionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Porcion|null
     */
    public function byId(int $id): ?Porcion;

    /**
     * @param Porcion $porcion
     * @return int
     */
    public function save(Porcion $porcion): int;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

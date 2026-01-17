<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Paquete;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface PaqueteRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Paquete|null
     */
    public function byId(int $id): ?Paquete;

    /**
     * @param Paquete $paquete
     * @return int
     */
    public function save(Paquete $paquete): int;

    /**
     * @return Paquete[]
     */
    public function list(): array;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

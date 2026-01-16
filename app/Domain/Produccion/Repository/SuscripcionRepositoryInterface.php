<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Suscripcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface SuscripcionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Suscripcion|null
     */
    public function byId(int $id): ?Suscripcion;

    /**
     * @param Suscripcion $suscripcion
     * @return int
     */
    public function save(Suscripcion $suscripcion): int;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

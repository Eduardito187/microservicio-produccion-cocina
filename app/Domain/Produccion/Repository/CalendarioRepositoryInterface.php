<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Calendario;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface CalendarioRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Calendario|null
     */
    public function byId(int $id): ?Calendario;

    /**
     * @param Calendario $calendario
     * @return int
     */
    public function save(Calendario $calendario): int;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

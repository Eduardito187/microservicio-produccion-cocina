<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\VentanaEntrega;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface VentanaEntregaRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return VentanaEntrega|null
     */
    public function byId(int $id): ?VentanaEntrega;

    /**
     * @param VentanaEntrega $ventanaEntrega
     * @return int
     */
    public function save(VentanaEntrega $ventanaEntrega): int;

    /**
     * @return VentanaEntrega[]
     */
    public function list(): array;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

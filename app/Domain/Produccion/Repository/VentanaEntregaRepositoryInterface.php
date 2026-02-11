<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\VentanaEntrega;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class VentanaEntregaRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface VentanaEntregaRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws ModelNotFoundException
     * @return VentanaEntrega|null
     */
    public function byId(string|int $id): ?VentanaEntrega;

    /**
     * @param VentanaEntrega $ventanaEntrega
     * @return int
     */
    public function save(VentanaEntrega $ventanaEntrega): string;

    /**
     * @return VentanaEntrega[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}

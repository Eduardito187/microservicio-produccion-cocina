<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class VentanaEntregaRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface VentanaEntregaRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws EntityNotFoundException
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

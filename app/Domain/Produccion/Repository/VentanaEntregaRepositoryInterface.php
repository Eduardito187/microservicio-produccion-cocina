<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class VentanaEntregaRepositoryInterface
 */
interface VentanaEntregaRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?VentanaEntrega;

    /**
     * @return int
     */
    public function save(VentanaEntrega $ventanaEntrega): string;

    /**
     * @return VentanaEntrega[]
     */
    public function list(): array;

    public function delete(string|int $id): void;
}

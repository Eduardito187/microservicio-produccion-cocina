<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\OrdenItem;

/**
 * @class OrdenItemRepositoryInterface
 */
interface OrdenItemRepositoryInterface
{
    public function byId(string $id): ?OrdenItem;

    public function save(OrdenItem $item): void;
}

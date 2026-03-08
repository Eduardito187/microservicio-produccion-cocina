<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\ItemDespacho;

/**
 * @class ItemDespachoRepositoryInterface
 */
interface ItemDespachoRepositoryInterface
{
    public function byId(string $id): ?ItemDespacho;

    public function save(ItemDespacho $item): void;
}

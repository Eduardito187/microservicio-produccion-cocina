<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\ItemDespacho;

/**
 * @class ItemDespachoRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface ItemDespachoRepositoryInterface
{
    /**
     * @param string $id
     * @return ItemDespacho|null
     */
    public function byId(string $id): ? ItemDespacho;

    /**
     * @param ItemDespacho $item
     * @return void
     */
    public function save(ItemDespacho $item): void;
}

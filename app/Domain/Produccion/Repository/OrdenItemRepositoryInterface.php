<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\OrdenItem;

/**
 * @class OrdenItemRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface OrdenItemRepositoryInterface
{
    /**
     * @param string $id
     * @return OrdenItem|null
     */
    public function byId(string $id): ? OrdenItem;

    /**
     * @param OrdenItem $item
     * @return void
     */
    public function save(OrdenItem $item): void;
}

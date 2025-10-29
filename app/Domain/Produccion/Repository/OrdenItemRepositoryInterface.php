<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\OrdenItem;

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
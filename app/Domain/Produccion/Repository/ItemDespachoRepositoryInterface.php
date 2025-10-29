<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\ItemDespacho;

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
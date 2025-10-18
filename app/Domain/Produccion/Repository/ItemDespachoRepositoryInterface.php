<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\ItemDespacho as AggregateItemDespacho;

interface ItemDespachoRepositoryInterface
{
    /**
     * @param string $id
     * @return AggregateItemDespacho|null
     */
    public function byId(string $id): ? AggregateItemDespacho;

    /**
     * @param AggregateItemDespacho $item
     * @return void
     */
    public function save(AggregateItemDespacho $item): void;
}
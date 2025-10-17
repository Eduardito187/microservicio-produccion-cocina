<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\OrdenItem as AggregateOrdenItem;

interface OrdenItemRepositoryInterface
{
    /**
     * @param string $id
     * @return AggregateOrdenItem|null
     */
    public function byId(string $id): ? AggregateOrdenItem;

    /**
     * @param AggregateOrdenItem $item
     * @return void
     */
    public function save(AggregateOrdenItem $item): void;
}
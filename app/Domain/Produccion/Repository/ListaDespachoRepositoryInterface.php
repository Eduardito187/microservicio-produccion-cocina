<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\ListaDespacho as AggregateListaDespacho;

interface ListaDespachoRepositoryInterface
{
    /**
     * @param string $id
     * @return AggregateListaDespacho|null
     */
    public function byId(string $id): ? AggregateListaDespacho;

    /**
     * @param AggregateListaDespacho $item
     * @param bool $resetItems
     * @return int
     */
    public function save(AggregateListaDespacho $item, bool $resetItems): int;
}
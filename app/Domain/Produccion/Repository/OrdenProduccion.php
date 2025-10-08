<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;

interface OrdenProduccion
{
    /**
     * @param string $id
     * @return AggregateOrdenProduccion|null
     */
    public function byId(string $id): ? AggregateOrdenProduccion;

    /**
     * @param AggregateOrdenProduccion $op
     * @return void
     */
    public function save(AggregateOrdenProduccion $op): void;
}
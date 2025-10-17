<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;

interface OrdenProduccionRepositoryInterface
{
    /**
     * @param int|null $id
     * @return AggregateOrdenProduccion|null
     */
    public function byId(int $id): ? AggregateOrdenProduccion;

    /**
     * @param AggregateOrdenProduccion $op
     * @return int
     */
    public function save(AggregateOrdenProduccion $op): int;
}
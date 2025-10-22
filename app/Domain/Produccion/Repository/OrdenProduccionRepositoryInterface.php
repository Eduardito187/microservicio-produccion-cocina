<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;

interface OrdenProduccionRepositoryInterface
{
    /**
     * @param int|null $id
     * @return AggregateOrdenProduccion|null
     */
    public function byId(int|null $id): ? AggregateOrdenProduccion;

    /**
     * @param AggregateOrdenProduccion $op
     * @param bool $resetItems
     * @param bool $sendOutbox
     * @return int
     */
    public function save(AggregateOrdenProduccion $op, bool $resetItems = false, bool $sendOutbox = false): int;
}
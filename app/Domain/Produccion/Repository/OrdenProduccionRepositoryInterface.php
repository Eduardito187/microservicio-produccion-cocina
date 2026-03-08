<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;

/**
 * @class OrdenProduccionRepositoryInterface
 */
interface OrdenProduccionRepositoryInterface
{
    public function byId(?string $id): ?AggregateOrdenProduccion;

    /**
     * @return int
     */
    public function save(AggregateOrdenProduccion $aggregateOrdenProduccion): string;
}

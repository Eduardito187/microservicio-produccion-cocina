<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use DateTimeImmutable;

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

    /**
     * Marca la orden de produccion como entrega completada si aun no lo esta.
     */
    public function markEntregaCompletada(string $opId, DateTimeImmutable $completedAt): void;
}

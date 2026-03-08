<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;

/**
 * @class ProduccionBatchRepositoryInterface
 */
interface ProduccionBatchRepositoryInterface
{
    public function byId(?string $id): ?AggregateProduccionBatch;

    /**
     * @return AggregateProduccionBatch[]
     */
    public function byOrderId(?string $ordenProduccionId): array;

    /**
     * @return int
     */
    public function save(AggregateProduccionBatch $pb): string;
}

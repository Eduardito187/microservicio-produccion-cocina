<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;

interface ProduccionBatchRepositoryInterface
{
    /**
     * @param int|null $id
     * @return AggregateProduccionBatch|null
     */
    public function byId(int|null $id): ? AggregateProduccionBatch;

    /**
     * @param int|null $ordenProduccionId
     * @return AggregateProduccionBatch[]
     */
    public function byOrderId(int|null $ordenProduccionId): array;

    /**
     * @param AggregateProduccionBatch $op
     * @return int
     */
    public function save(AggregateProduccionBatch $op): int;
}
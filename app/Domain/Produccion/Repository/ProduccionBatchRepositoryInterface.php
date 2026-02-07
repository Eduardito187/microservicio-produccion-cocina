<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;

interface ProduccionBatchRepositoryInterface
{
    /**
     * @param string|null $id
     * @return AggregateProduccionBatch|null
     */
    public function byId(string|null $id): ? AggregateProduccionBatch;

    /**
     * @param string|null $ordenProduccionId
     * @return AggregateProduccionBatch[]
     */
    public function byOrderId(string|null $ordenProduccionId): array;

    /**
     * @param AggregateProduccionBatch $pb
     * @return int
     */
    public function save(AggregateProduccionBatch $pb): string;
}
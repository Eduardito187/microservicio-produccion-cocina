<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Aggregate\EstadoPlanificado;
use App\Application\Produccion\Command\PlanificarOP;

class PlanificadorOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    public readonly OrdenProduccionRepositoryInterface $ordenProduccionRepository;

    /**
     * @var ProduccionBatchRepositoryInterface
     */
    public readonly ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $ordenProduccionRepository
     * @param ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepository,
        ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ordenProduccionRepository = $ordenProduccionRepository;
        $this->produccionBatchRepositoryInterface = $produccionBatchRepositoryInterface;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param PlanificarOP $command
     * @return string|int|null
     */
    public function __invoke(PlanificarOP $command): string|int|null
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $ordenProduccion = $this->ordenProduccionRepository->byId($command->opId);

            foreach ($ordenProduccion->items() as $key => $item) {
                $this->produccionBatchRepositoryInterface->save(
                    new AggregateProduccionBatch(
                        null,
                        $command->opId,
                        1,
                        1,
                        1,
                        1,
                        1,
                        50,
                        EstadoPlanificado::PROGRAMADO,
                        $item->sku,
                        $item->qty,
                        $key + 1,
                        []
                    )
                );
            }

            $ordenProduccion->planificar();
            return $this->ordenProduccionRepository->save($ordenProduccion, false, true);
        });
    }
}
<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\PlanificarOP;

class PlanificadorOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    public readonly OrdenProduccionRepositoryInterface $ordenProduccionRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $ordenProduccionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ordenProduccionRepository = $ordenProduccionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param PlanificarOP $command
     * @return string|int|null
     */
    public function __invoke(PlanificarOP $command): string|int|null
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $ordenProduccion = $this->ordenProduccionRepository->byId($command->ordenProduccionId);
            $ordenProduccion->generarBatches($command);
            $ordenProduccion->planificar();
            return $this->ordenProduccionRepository->save($ordenProduccion);
        });
    }
}









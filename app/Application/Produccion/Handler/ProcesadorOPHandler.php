<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ProcesadorOP;

class ProcesadorOPHandler
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
     * @param ProcesadorOP $command
     * @return string|int|null
     */
    public function __invoke(ProcesadorOP $command): string|int|null
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $ordenProduccion = $this->ordenProduccionRepository->byId($command->opId);
            $ordenProduccion->procesarBatches();
            $ordenProduccion->procesar();
            return $this->ordenProduccionRepository->save($ordenProduccion);
        });
    }
}
<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarPaquete;

class EliminarPaqueteHandler
{
    /**
     * @var PaqueteRepositoryInterface
     */
    public readonly PaqueteRepositoryInterface $paqueteRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param PaqueteRepositoryInterface $paqueteRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        PaqueteRepositoryInterface $paqueteRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->paqueteRepository = $paqueteRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param EliminarPaquete $command
     * @return void
     */
    public function __invoke(EliminarPaquete $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->paqueteRepository->byId($command->id);
            $this->paqueteRepository->delete($command->id);
        });
    }
}









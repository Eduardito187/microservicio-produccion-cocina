<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EstacionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarEstacion;

class EliminarEstacionHandler
{
    /**
     * @var EstacionRepositoryInterface
     */
    public readonly EstacionRepositoryInterface $estacionRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param EstacionRepositoryInterface $estacionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        EstacionRepositoryInterface $estacionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->estacionRepository = $estacionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param EliminarEstacion $command
     * @return void
     */
    public function __invoke(EliminarEstacion $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->estacionRepository->byId($command->id);
            $this->estacionRepository->delete($command->id);
        });
    }
}

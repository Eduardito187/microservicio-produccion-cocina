<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarDireccion;

class EliminarDireccionHandler
{
    /**
     * @var DireccionRepositoryInterface
     */
    public readonly DireccionRepositoryInterface $direccionRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param DireccionRepositoryInterface $direccionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param EliminarDireccion $command
     * @return void
     */
    public function __invoke(EliminarDireccion $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->direccionRepository->byId($command->id);
            $this->direccionRepository->delete($command->id);
        });
    }
}









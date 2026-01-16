<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PorcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarPorcion;

class ActualizarPorcionHandler
{
    /**
     * @var PorcionRepositoryInterface
     */
    public readonly PorcionRepositoryInterface $porcionRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param PorcionRepositoryInterface $porcionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        PorcionRepositoryInterface $porcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->porcionRepository = $porcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param ActualizarPorcion $command
     * @return int
     */
    public function __invoke(ActualizarPorcion $command): int
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $porcion = $this->porcionRepository->byId($command->id);
            $porcion->nombre = $command->nombre;
            $porcion->pesoGr = $command->pesoGr;

            return $this->porcionRepository->save($porcion);
        });
    }
}

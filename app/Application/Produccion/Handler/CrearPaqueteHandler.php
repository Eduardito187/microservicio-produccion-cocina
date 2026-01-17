<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearPaquete;
use App\Domain\Produccion\Entity\Paquete;

class CrearPaqueteHandler
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
     * @param CrearPaquete $command
     * @return int
     */
    public function __invoke(CrearPaquete $command): int
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $paquete = new Paquete(
                null,
                $command->etiquetaId,
                $command->ventanaId,
                $command->direccionId
            );

            return $this->paqueteRepository->save($paquete);
        });
    }
}









<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarPaquete;

class ActualizarPaqueteHandler
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
     * @param ActualizarPaquete $command
     * @return int
     */
    public function __invoke(ActualizarPaquete $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $paquete = $this->paqueteRepository->byId($command->id);
            $paquete->etiquetaId = $command->etiquetaId;
            $paquete->ventanaId = $command->ventanaId;
            $paquete->direccionId = $command->direccionId;

            return $this->paqueteRepository->save($paquete);
        });
    }
}









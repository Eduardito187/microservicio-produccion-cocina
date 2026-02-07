<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarSuscripcion;

class ActualizarSuscripcionHandler
{
    /**
     * @var SuscripcionRepositoryInterface
     */
    public readonly SuscripcionRepositoryInterface $suscripcionRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param SuscripcionRepositoryInterface $suscripcionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->suscripcionRepository = $suscripcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param ActualizarSuscripcion $command
     * @return int
     */
    public function __invoke(ActualizarSuscripcion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $suscripcion = $this->suscripcionRepository->byId($command->id);
            $suscripcion->nombre = $command->nombre;

            return $this->suscripcionRepository->save($suscripcion);
        });
    }
}









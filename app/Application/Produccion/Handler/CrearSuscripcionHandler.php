<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearSuscripcion;
use App\Domain\Produccion\Entity\Suscripcion;

class CrearSuscripcionHandler
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
     * @param CrearSuscripcion $command
     * @return int
     */
    public function __invoke(CrearSuscripcion $command): int
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $suscripcion = new Suscripcion(null, $command->nombre);

            return $this->suscripcionRepository->save($suscripcion);
        });
    }
}









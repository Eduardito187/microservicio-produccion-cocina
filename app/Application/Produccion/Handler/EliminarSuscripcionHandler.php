<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarSuscripcion;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;

/**
 * @class EliminarSuscripcionHandler
 */
class EliminarSuscripcionHandler
{
    /**
     * @var SuscripcionRepositoryInterface
     */
    private $suscripcionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->suscripcionRepository = $suscripcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(EliminarSuscripcion $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->suscripcionRepository->byId($command->id);
            $this->suscripcionRepository->delete($command->id);
        });
    }
}

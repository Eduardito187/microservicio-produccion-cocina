<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarSuscripcion;

/**
 * @class EliminarSuscripcionHandler
 * @package App\Application\Produccion\Handler
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
     * @param EliminarSuscripcion $command
     * @return void
     */
    public function __invoke(EliminarSuscripcion $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->suscripcionRepository->byId($command->id);
            $this->suscripcionRepository->delete($command->id);
        });
    }
}

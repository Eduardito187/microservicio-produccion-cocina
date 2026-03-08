<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarPaquete;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;

/**
 * @class EliminarPaqueteHandler
 */
class EliminarPaqueteHandler
{
    /**
     * @var PaqueteRepositoryInterface
     */
    private $paqueteRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        PaqueteRepositoryInterface $paqueteRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->paqueteRepository = $paqueteRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(EliminarPaquete $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->paqueteRepository->byId($command->id);
            $this->paqueteRepository->delete($command->id);
        });
    }
}

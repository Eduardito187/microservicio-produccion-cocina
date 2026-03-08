<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarPorcion;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\PorcionRepositoryInterface;

/**
 * @class EliminarPorcionHandler
 */
class EliminarPorcionHandler
{
    /**
     * @var PorcionRepositoryInterface
     */
    private $porcionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        PorcionRepositoryInterface $porcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->porcionRepository = $porcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(EliminarPorcion $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->porcionRepository->byId($command->id);
            $this->porcionRepository->delete($command->id);
        });
    }
}

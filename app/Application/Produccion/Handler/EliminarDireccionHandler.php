<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarDireccion;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;

/**
 * @class EliminarDireccionHandler
 */
class EliminarDireccionHandler
{
    /**
     * @var DireccionRepositoryInterface
     */
    private $direccionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(EliminarDireccion $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->direccionRepository->byId($command->id);
            $this->direccionRepository->delete($command->id);
        });
    }
}

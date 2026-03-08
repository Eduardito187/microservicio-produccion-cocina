<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\GenerarOP;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;

/**
 * @class GenerarOPHandler
 */
class GenerarOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    private $ordenProduccionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ordenProduccionRepository = $ordenProduccionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(GenerarOP $command): string|int|null
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $ordenProduccion = AggregateOrdenProduccion::crear(
                $command->fecha
            );
            $ordenProduccion->agregarItems($command->items);

            return $this->ordenProduccionRepository->save($ordenProduccion);
        });
    }
}

<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\GenerarOP;

class GenerarOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    public readonly OrdenProduccionRepositoryInterface $ordenProduccionRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $ordenProduccionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ordenProduccionRepository = $ordenProduccionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param GenerarOP $command
     * @return string|int|null
     */
    public function __invoke(GenerarOP $command): string|int|null
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $ordenProduccion = AggregateOrdenProduccion::crear( 
                $command->fecha,
                $command->sucursalId
            );
            $ordenProduccion->agregarItems($command->items);
            return $this->ordenProduccionRepository->save($ordenProduccion);
        });
    }
}









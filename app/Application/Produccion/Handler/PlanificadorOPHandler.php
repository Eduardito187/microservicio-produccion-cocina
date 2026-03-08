<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\PlanificarOP;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;

/**
 * @class PlanificadorOPHandler
 */
class PlanificadorOPHandler
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

    public function __invoke(PlanificarOP $command): string|int|null
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $ordenProduccion = $this->ordenProduccionRepository->byId($command->ordenProduccionId);
            $ordenProduccion->generarBatches(
                $command->porcionId
            );
            $ordenProduccion->planificar();

            return $this->ordenProduccionRepository->save($ordenProduccion);
        });
    }
}

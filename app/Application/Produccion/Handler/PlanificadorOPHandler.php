<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\PlanificarOP;

/**
 * @class PlanificadorOPHandler
 * @package App\Application\Produccion\Handler
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
     * @param PlanificarOP $command
     * @return string|int|null
     */
    public function __invoke(PlanificarOP $command): string|int|null
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $ordenProduccion = $this->ordenProduccionRepository->byId($command->ordenProduccionId);
            $ordenProduccion->generarBatches(
                $command->estacionId,
                $command->recetaVersionId,
                $command->porcionId
            );
            $ordenProduccion->planificar();
            return $this->ordenProduccionRepository->save($ordenProduccion);
        });
    }
}

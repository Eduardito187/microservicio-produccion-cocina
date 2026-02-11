<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PorcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarPorcion;

/**
 * @class EliminarPorcionHandler
 * @package App\Application\Produccion\Handler
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
     *
     * @param PorcionRepositoryInterface $porcionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        PorcionRepositoryInterface $porcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->porcionRepository = $porcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param EliminarPorcion $command
     * @return void
     */
    public function __invoke(EliminarPorcion $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->porcionRepository->byId($command->id);
            $this->porcionRepository->delete($command->id);
        });
    }
}

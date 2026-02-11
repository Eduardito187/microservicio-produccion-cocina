<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarRecetaVersion;

/**
 * @class EliminarRecetaVersionHandler
 * @package App\Application\Produccion\Handler
 */
class EliminarRecetaVersionHandler
{
    /**
     * @var RecetaVersionRepositoryInterface
     */
    private $recetaVersionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     *
     * @param RecetaVersionRepositoryInterface $recetaVersionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        RecetaVersionRepositoryInterface $recetaVersionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->recetaVersionRepository = $recetaVersionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param EliminarRecetaVersion $command
     * @return void
     */
    public function __invoke(EliminarRecetaVersion $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->recetaVersionRepository->byId($command->id);
            $this->recetaVersionRepository->delete($command->id);
        });
    }
}

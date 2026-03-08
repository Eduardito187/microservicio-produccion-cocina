<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarRecetaVersion;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;

/**
 * @class EliminarRecetaVersionHandler
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
     */
    public function __construct(
        RecetaVersionRepositoryInterface $recetaVersionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->recetaVersionRepository = $recetaVersionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(EliminarRecetaVersion $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->recetaVersionRepository->byId($command->id);
            $this->recetaVersionRepository->delete($command->id);
        });
    }
}

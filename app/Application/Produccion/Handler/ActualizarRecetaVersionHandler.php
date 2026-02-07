<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarRecetaVersion;

class ActualizarRecetaVersionHandler
{
    /**
     * @var RecetaVersionRepositoryInterface
     */
    public readonly RecetaVersionRepositoryInterface $recetaVersionRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

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
     * @param ActualizarRecetaVersion $command
     * @return int
     */
    public function __invoke(ActualizarRecetaVersion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $recetaVersion = $this->recetaVersionRepository->byId($command->id);
            $recetaVersion->nombre = $command->nombre;
            $recetaVersion->nutrientes = $command->nutrientes;
            $recetaVersion->ingredientes = $command->ingredientes;
            $recetaVersion->version = $command->version;

            return $this->recetaVersionRepository->save($recetaVersion);
        });
    }
}









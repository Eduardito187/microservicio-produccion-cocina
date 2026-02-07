<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearRecetaVersion;
use App\Domain\Produccion\Entity\RecetaVersion;

class CrearRecetaVersionHandler
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
     * @param CrearRecetaVersion $command
     * @return int
     */
    public function __invoke(CrearRecetaVersion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $recetaVersion = new RecetaVersion(
                null,
                $command->nombre,
                $command->nutrientes,
                $command->ingredientes,
                $command->version
            );

            return $this->recetaVersionRepository->save($recetaVersion);
        });
    }
}









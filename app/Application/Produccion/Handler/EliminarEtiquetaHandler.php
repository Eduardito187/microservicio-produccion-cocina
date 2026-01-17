<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarEtiqueta;

class EliminarEtiquetaHandler
{
    /**
     * @var EtiquetaRepositoryInterface
     */
    public readonly EtiquetaRepositoryInterface $etiquetaRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param EtiquetaRepositoryInterface $etiquetaRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        EtiquetaRepositoryInterface $etiquetaRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->etiquetaRepository = $etiquetaRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param EliminarEtiqueta $command
     * @return void
     */
    public function __invoke(EliminarEtiqueta $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->etiquetaRepository->byId($command->id);
            $this->etiquetaRepository->delete($command->id);
        });
    }
}









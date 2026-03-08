<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarEtiqueta;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;

/**
 * @class EliminarEtiquetaHandler
 */
class EliminarEtiquetaHandler
{
    /**
     * @var EtiquetaRepositoryInterface
     */
    private $etiquetaRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        EtiquetaRepositoryInterface $etiquetaRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->etiquetaRepository = $etiquetaRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(EliminarEtiqueta $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->etiquetaRepository->byId($command->id);
            $this->etiquetaRepository->delete($command->id);
        });
    }
}

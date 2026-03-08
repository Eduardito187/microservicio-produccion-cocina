<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ActualizarEtiqueta;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;

/**
 * @class ActualizarEtiquetaHandler
 */
class ActualizarEtiquetaHandler
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

    /**
     * @return int
     */
    public function __invoke(ActualizarEtiqueta $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $etiqueta = $this->etiquetaRepository->byId($command->id);
            $etiqueta->suscripcionId = $command->suscripcionId;
            $etiqueta->pacienteId = $command->pacienteId;
            $etiqueta->qrPayload = $command->qrPayload;

            return $this->etiquetaRepository->save($etiqueta);
        });
    }
}

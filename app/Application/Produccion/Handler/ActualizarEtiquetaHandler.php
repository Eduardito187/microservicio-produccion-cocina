<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarEtiqueta;

class ActualizarEtiquetaHandler
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
     * @param ActualizarEtiqueta $command
     * @return int
     */
    public function __invoke(ActualizarEtiqueta $command): int
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $etiqueta = $this->etiquetaRepository->byId($command->id);
            $etiqueta->recetaVersionId = $command->recetaVersionId;
            $etiqueta->suscripcionId = $command->suscripcionId;
            $etiqueta->pacienteId = $command->pacienteId;
            $etiqueta->qrPayload = $command->qrPayload;

            return $this->etiquetaRepository->save($etiqueta);
        });
    }
}









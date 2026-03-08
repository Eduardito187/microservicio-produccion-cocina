<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\CrearEtiqueta;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Etiqueta;
use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;

/**
 * @class CrearEtiquetaHandler
 */
class CrearEtiquetaHandler
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
    public function __invoke(CrearEtiqueta $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $etiqueta = new Etiqueta(
                null,
                $command->suscripcionId,
                $command->pacienteId,
                $command->qrPayload
            );

            return $this->etiquetaRepository->save($etiqueta);
        });
    }
}

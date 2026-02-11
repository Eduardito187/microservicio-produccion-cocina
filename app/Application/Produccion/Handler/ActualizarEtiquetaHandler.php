<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarEtiqueta;

/**
 * @class ActualizarEtiquetaHandler
 * @package App\Application\Produccion\Handler
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
    public function __invoke(ActualizarEtiqueta $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $etiqueta = $this->etiquetaRepository->byId($command->id);
            $etiqueta->recetaVersionId = $command->recetaVersionId;
            $etiqueta->suscripcionId = $command->suscripcionId;
            $etiqueta->pacienteId = $command->pacienteId;
            $etiqueta->qrPayload = $command->qrPayload;

            return $this->etiquetaRepository->save($etiqueta);
        });
    }
}

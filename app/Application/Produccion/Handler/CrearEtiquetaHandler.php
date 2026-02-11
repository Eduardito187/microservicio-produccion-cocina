<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearEtiqueta;
use App\Domain\Produccion\Entity\Etiqueta;

/**
 * @class CrearEtiquetaHandler
 * @package App\Application\Produccion\Handler
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
     * @param CrearEtiqueta $command
     * @return int
     */
    public function __invoke(CrearEtiqueta $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $etiqueta = new Etiqueta(
                null,
                $command->recetaVersionId,
                $command->suscripcionId,
                $command->pacienteId,
                $command->qrPayload
            );

            return $this->etiquetaRepository->save($etiqueta);
        });
    }
}

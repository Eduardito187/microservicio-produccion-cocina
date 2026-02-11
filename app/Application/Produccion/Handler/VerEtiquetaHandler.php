<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerEtiqueta;
use App\Domain\Produccion\Entity\Etiqueta;

/**
 * @class VerEtiquetaHandler
 * @package App\Application\Produccion\Handler
 */
class VerEtiquetaHandler
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
     * @param VerEtiqueta $command
     * @return array
     */
    public function __invoke(VerEtiqueta $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $etiqueta = $this->etiquetaRepository->byId($command->id);
            return $this->mapEtiqueta($etiqueta);
        });
    }

    /**
     * @param Etiqueta $etiqueta
     * @return array
     */
    private function mapEtiqueta(Etiqueta $etiqueta): array
    {
        return [
            'id' => $etiqueta->id,
            'receta_version_id' => $etiqueta->recetaVersionId,
            'suscripcion_id' => $etiqueta->suscripcionId,
            'paciente_id' => $etiqueta->pacienteId,
            'qr_payload' => $etiqueta->qrPayload,
        ];
    }
}

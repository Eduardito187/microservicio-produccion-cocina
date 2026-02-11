<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ListarEtiquetas;
use App\Domain\Produccion\Entity\Etiqueta;

/**
 * @class ListarEtiquetasHandler
 * @package App\Application\Produccion\Handler
 */
class ListarEtiquetasHandler
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
     * @param ListarEtiquetas $command
     * @return array
     */
    public function __invoke(ListarEtiquetas $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapEtiqueta'], $this->etiquetaRepository->list());
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

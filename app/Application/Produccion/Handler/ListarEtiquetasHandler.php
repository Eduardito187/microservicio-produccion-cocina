<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarEtiquetas;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Etiqueta;
use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;

/**
 * @class ListarEtiquetasHandler
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
     */
    public function __construct(
        EtiquetaRepositoryInterface $etiquetaRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->etiquetaRepository = $etiquetaRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarEtiquetas $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapEtiqueta'], $this->etiquetaRepository->list());
        });
    }

    private function mapEtiqueta(Etiqueta $etiqueta): array
    {
        return [
            'id' => $etiqueta->id,
            'suscripcion_id' => $etiqueta->suscripcionId,
            'paciente_id' => $etiqueta->pacienteId,
            'qr_payload' => $etiqueta->qrPayload,
        ];
    }
}

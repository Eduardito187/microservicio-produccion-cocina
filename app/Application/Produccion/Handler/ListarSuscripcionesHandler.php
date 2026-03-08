<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarSuscripciones;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;

/**
 * @class ListarSuscripcionesHandler
 */
class ListarSuscripcionesHandler
{
    /**
     * @var SuscripcionRepositoryInterface
     */
    private $suscripcionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->suscripcionRepository = $suscripcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarSuscripciones $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapSuscripcion'], $this->suscripcionRepository->list());
        });
    }

    private function mapSuscripcion(Suscripcion $suscripcion): array
    {
        return [
            'id' => $suscripcion->id,
            'nombre' => $suscripcion->nombre,
            'paciente_id' => $suscripcion->pacienteId,
            'tipo_servicio' => $suscripcion->tipoServicio,
            'fecha_inicio' => $suscripcion->fechaInicio,
            'fecha_fin' => $suscripcion->fechaFin,
            'estado' => $suscripcion->estado,
            'motivo_cancelacion' => $suscripcion->motivoCancelacion,
            'cancelado_at' => $suscripcion->canceladoAt,
        ];
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\SuscripcionActualizadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class SuscripcionActualizadaHandler
 */
class SuscripcionActualizadaHandler implements IntegrationEventHandlerInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct(
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->suscripcionRepository = $suscripcionRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = SuscripcionActualizadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $existing = null;
            try {
                $existing = $this->suscripcionRepository->byId($event->id);
            } catch (EntityNotFoundException $e) {
                $existing = null;
            }

            if ($existing === null && $event->nombre === null) {
                $this->logger->warning('Actualizacion de suscripcion ignorada (falta nombre para crear)', [
                    'suscripcion_id' => $event->id,
                ]);

                return;
            }

            $suscripcion = $existing ?? new Suscripcion(
                $event->id,
                $event->nombre ?? ''
            );

            if ($event->nombre !== null) {
                $suscripcion->nombre = $event->nombre;
            }
            if ($event->pacienteId !== null) {
                $suscripcion->pacienteId = $event->pacienteId;
            }
            if ($event->tipoServicio !== null) {
                $suscripcion->tipoServicio = $event->tipoServicio;
            }
            if ($event->fechaInicio !== null) {
                $suscripcion->fechaInicio = $event->fechaInicio;
            }
            if ($event->fechaFin !== null) {
                $suscripcion->fechaFin = $event->fechaFin;
            }
            if ($suscripcion->estado === null || $suscripcion->estado === '') {
                $suscripcion->estado = 'ACTIVA';
            }

            $this->suscripcionRepository->save($suscripcion);
        });
    }
}

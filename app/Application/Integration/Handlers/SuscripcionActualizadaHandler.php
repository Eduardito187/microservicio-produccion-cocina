<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Integration\Events\SuscripcionActualizadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Produccion\Entity\Suscripcion;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class SuscripcionActualizadaHandler
 * @package App\Application\Integration\Handlers
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
     *
     * @param SuscripcionRepositoryInterface $suscripcionRepository
     * @param TransactionAggregate $transactionAggregate
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->suscripcionRepository = $suscripcionRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
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
                $this->logger->warning('Suscripcion update ignored (missing nombre for create)', [
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

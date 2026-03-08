<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\SuscripcionCreadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;

/**
 * @class SuscripcionCreadaHandler
 */
class SuscripcionCreadaHandler implements IntegrationEventHandlerInterface
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

    public function handle(array $payload, array $meta = []): void
    {
        $event = SuscripcionCreadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $suscripcion = new Suscripcion(
                $event->id,
                $event->nombre,
                $event->pacienteId,
                $event->tipoServicio,
                $event->fechaInicio,
                $event->fechaFin,
                'ACTIVA'
            );
            $this->suscripcionRepository->save($suscripcion);
        });
    }
}

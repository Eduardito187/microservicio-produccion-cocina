<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\SuscripcionActualizadaEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SuscripcionActualizadaHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly SuscripcionRepositoryInterface $suscripcionRepository,
        private readonly TransactionAggregate $transactionAggregate
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = SuscripcionActualizadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $existing = null;
            try {
                $existing = $this->suscripcionRepository->byId($event->id);
            } catch (ModelNotFoundException $e) {
                $existing = null;
            }

            if ($existing === null && $event->nombre === null) {
                logger()->warning('Suscripcion update ignored (missing nombre for create)', [
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

            $this->suscripcionRepository->save($suscripcion);
        });
    }
}

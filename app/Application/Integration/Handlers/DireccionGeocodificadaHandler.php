<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\DireccionGeocodificadaEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DireccionGeocodificadaHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly DireccionRepositoryInterface $direccionRepository,
        private readonly TransactionAggregate $transactionAggregate
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = DireccionGeocodificadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            if ($event->geo === null) {
                logger()->warning('Direccion geocodificada ignored (missing geo)', [
                    'direccion_id' => $event->id,
                ]);
                return;
            }

            try {
                $direccion = $this->direccionRepository->byId($event->id);
            } catch (ModelNotFoundException $e) {
                logger()->warning('Direccion geocodificada ignored (direccion not found)', [
                    'direccion_id' => $event->id,
                ]);
                return;
            }

            $direccion->geo = $event->geo;
            $this->direccionRepository->save($direccion);
        });
    }
}

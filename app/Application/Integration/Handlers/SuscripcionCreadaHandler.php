<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\SuscripcionCreadaEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;

class SuscripcionCreadaHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly SuscripcionRepositoryInterface $suscripcionRepository,
        private readonly TransactionAggregate $transactionAggregate
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = SuscripcionCreadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $suscripcion = new Suscripcion(
                $event->id,
                $event->nombre
            );
            $this->suscripcionRepository->save($suscripcion);
        });
    }
}

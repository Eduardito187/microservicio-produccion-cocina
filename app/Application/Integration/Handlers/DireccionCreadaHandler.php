<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\DireccionCreadaEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Direccion;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;

class DireccionCreadaHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly DireccionRepositoryInterface $direccionRepository,
        private readonly TransactionAggregate $transactionAggregate
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = DireccionCreadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $direccion = new Direccion(
                $event->id,
                $event->nombre,
                $event->linea1,
                $event->linea2,
                $event->ciudad,
                $event->provincia,
                $event->pais,
                $event->geo
            );
            $this->direccionRepository->save($direccion);
        });
    }
}

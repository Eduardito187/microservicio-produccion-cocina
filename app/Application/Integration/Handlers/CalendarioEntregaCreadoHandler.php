<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\CalendarioEntregaCreadoEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use DateTimeImmutable;

class CalendarioEntregaCreadoHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly CalendarioRepositoryInterface $calendarioRepository,
        private readonly TransactionAggregate $transactionAggregate
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = CalendarioEntregaCreadoEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $calendario = new Calendario(
                $event->id,
                new DateTimeImmutable($event->fecha),
                $event->sucursalId
            );

            $this->calendarioRepository->save($calendario);
        });
    }
}

<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\DiaSinEntregaMarcadoEvent;
use App\Application\Integration\CalendarProcessManager;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DiaSinEntregaMarcadoHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly CalendarioRepositoryInterface $calendarioRepository,
        private readonly CalendarioItemRepositoryInterface $calendarioItemRepository,
        private readonly TransactionAggregate $transactionAggregate,
        private readonly CalendarProcessManager $calendarProcessManager
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = DiaSinEntregaMarcadoEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            try {
                $this->calendarioRepository->byId($event->calendarioId);
            } catch (ModelNotFoundException $e) {
                return;
            }

            $this->calendarioItemRepository->deleteByCalendarioId($event->calendarioId);
            $this->calendarioRepository->delete($event->calendarioId);
        });

        $this->calendarProcessManager->onDiaSinEntregaMarcado($payload);
    }
}

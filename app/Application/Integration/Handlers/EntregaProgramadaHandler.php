<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\EntregaProgramadaEvent;
use App\Application\Integration\CalendarProcessManager;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\CalendarioItem;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EntregaProgramadaHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly CalendarioItemRepositoryInterface $calendarioItemRepository,
        private readonly TransactionAggregate $transactionAggregate,
        private readonly CalendarProcessManager $calendarProcessManager,
        private readonly ItemDespachoRepositoryInterface $itemDespachoRepository
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = EntregaProgramadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            try {
                $this->itemDespachoRepository->byId($event->itemDespachoId);
            } catch (ModelNotFoundException $e) {
                logger()->warning('EntregaProgramada ignored (item_despacho not found)', [
                    'item_despacho_id' => $event->itemDespachoId,
                ]);
                return;
            }

            $item = new CalendarioItem(
                null,
                $event->calendarioId,
                $event->itemDespachoId
            );
            $this->calendarioItemRepository->save($item);
        });

        $this->calendarProcessManager->onEntregaProgramada($payload);
    }
}

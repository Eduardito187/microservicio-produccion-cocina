<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\CalendarProcessManager;
use App\Application\Integration\Events\EntregaProgramadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\CalendarioItem;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class EntregaProgramadaHandler
 */
class EntregaProgramadaHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var CalendarioItemRepositoryInterface
     */
    private $calendarioItemRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * @var CalendarProcessManager
     */
    private $calendarProcessManager;

    /**
     * @var ItemDespachoRepositoryInterface
     */
    private $itemDespachoRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct(
        CalendarioItemRepositoryInterface $calendarioItemRepository,
        TransactionAggregate $transactionAggregate,
        CalendarProcessManager $calendarProcessManager,
        ItemDespachoRepositoryInterface $itemDespachoRepository,
        ?LoggerInterface $logger = null
    ) {
        $this->calendarioItemRepository = $calendarioItemRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->calendarProcessManager = $calendarProcessManager;
        $this->itemDespachoRepository = $itemDespachoRepository;
        $this->logger = $logger ?? new NullLogger;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = EntregaProgramadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            try {
                $this->itemDespachoRepository->byId($event->itemDespachoId);
            } catch (EntityNotFoundException $e) {
                $this->logger->warning('EntregaProgramada ignorada (item_despacho no encontrado)', [
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

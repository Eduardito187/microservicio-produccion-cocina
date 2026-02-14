<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\EntregaProgramadaEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Application\Integration\CalendarProcessManager;
use App\Domain\Produccion\Entity\CalendarioItem;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class EntregaProgramadaHandler
 * @package App\Application\Integration\Handlers
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
     *
     * @param CalendarioItemRepositoryInterface $calendarioItemRepository
     * @param TransactionAggregate $transactionAggregate
     * @param CalendarProcessManager $calendarProcessManager
     * @param ItemDespachoRepositoryInterface $itemDespachoRepository
     * @param ?LoggerInterface $logger
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
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $event = EntregaProgramadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            try {
                $this->itemDespachoRepository->byId($event->itemDespachoId);
            } catch (EntityNotFoundException $e) {
                $this->logger->warning('EntregaProgramada ignored (item_despacho not found)', [
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

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\CalendarProcessManager;
use App\Application\Integration\Events\DiaSinEntregaMarcadoEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class DiaSinEntregaMarcadoHandler
 */
class DiaSinEntregaMarcadoHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var CalendarioRepositoryInterface
     */
    private $calendarioRepository;

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
     * Constructor
     */
    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        CalendarioItemRepositoryInterface $calendarioItemRepository,
        TransactionAggregate $transactionAggregate,
        CalendarProcessManager $calendarProcessManager
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->calendarioItemRepository = $calendarioItemRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->calendarProcessManager = $calendarProcessManager;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = DiaSinEntregaMarcadoEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            try {
                $this->calendarioRepository->byId($event->calendarioId);
            } catch (EntityNotFoundException $e) {
                return;
            }

            $this->calendarioItemRepository->deleteByCalendarioId($event->calendarioId);
            $this->calendarioRepository->delete($event->calendarioId);
        });

        $this->calendarProcessManager->onDiaSinEntregaMarcado($payload);
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\DiaSinEntregaMarcadoEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Integration\CalendarProcessManager;

/**
 * @class DiaSinEntregaMarcadoHandler
 * @package App\Application\Integration\Handlers
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
     *
     * @param CalendarioRepositoryInterface $calendarioRepository
     * @param CalendarioItemRepositoryInterface $calendarioItemRepository
     * @param TransactionAggregate $transactionAggregate
     * @param CalendarProcessManager $calendarProcessManager
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

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
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

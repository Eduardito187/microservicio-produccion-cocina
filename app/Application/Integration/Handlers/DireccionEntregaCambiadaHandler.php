<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\DireccionEntregaCambiadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Application\Integration\CalendarProcessManager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class DireccionEntregaCambiadaHandler
 * @package App\Application\Integration\Handlers
 */
class DireccionEntregaCambiadaHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var PaqueteRepositoryInterface
     */
    private $paqueteRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * @var CalendarProcessManager
     */
    private $calendarProcessManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param PaqueteRepositoryInterface $paqueteRepository
     * @param TransactionAggregate $transactionAggregate
     * @param CalendarProcessManager $calendarProcessManager
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        PaqueteRepositoryInterface $paqueteRepository,
        TransactionAggregate $transactionAggregate,
        CalendarProcessManager $calendarProcessManager,
        ?LoggerInterface $logger = null
    ) {
        $this->paqueteRepository = $paqueteRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->calendarProcessManager = $calendarProcessManager;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $event = DireccionEntregaCambiadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            if ($event->paqueteId === null) {
                $this->logger->warning('DireccionEntregaCambiada ignored (missing paqueteId)');
                return;
            }

            try {
                $paquete = $this->paqueteRepository->byId($event->paqueteId);
            } catch (EntityNotFoundException $e) {
                $this->logger->warning('DireccionEntregaCambiada ignored (paquete not found)', [
                    'paquete_id' => $event->paqueteId,
                ]);
                return;
            }

            $paquete->direccionId = $event->direccionId;
            $this->paqueteRepository->save($paquete);
        });

        $this->calendarProcessManager->onDireccionEntregaCambiada($payload);
    }
}

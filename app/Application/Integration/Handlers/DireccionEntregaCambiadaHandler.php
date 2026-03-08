<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\CalendarProcessManager;
use App\Application\Integration\Events\DireccionEntregaCambiadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class DireccionEntregaCambiadaHandler
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
        $this->logger = $logger ?? new NullLogger;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = DireccionEntregaCambiadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            if ($event->paqueteId === null) {
                $this->logger->warning('DireccionEntregaCambiada ignorada (falta paqueteId)');

                return;
            }

            try {
                $paquete = $this->paqueteRepository->byId($event->paqueteId);
            } catch (EntityNotFoundException $e) {
                $this->logger->warning('DireccionEntregaCambiada ignorada (paquete no encontrado)', [
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

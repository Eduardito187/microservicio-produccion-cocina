<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\DireccionEntregaCambiadaEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Integration\CalendarProcessManager;

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
     * Constructor
     *
     * @param PaqueteRepositoryInterface $paqueteRepository
     * @param TransactionAggregate $transactionAggregate
     * @param CalendarProcessManager $calendarProcessManager
     */
    public function __construct(
        PaqueteRepositoryInterface $paqueteRepository,
        TransactionAggregate $transactionAggregate,
        CalendarProcessManager $calendarProcessManager
    ) {
        $this->paqueteRepository = $paqueteRepository;
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
        $event = DireccionEntregaCambiadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            if ($event->paqueteId === null) {
                logger()->warning('DireccionEntregaCambiada ignored (missing paqueteId)');
                return;
            }

            try {
                $paquete = $this->paqueteRepository->byId($event->paqueteId);
            } catch (ModelNotFoundException $e) {
                logger()->warning('DireccionEntregaCambiada ignored (paquete not found)', [
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

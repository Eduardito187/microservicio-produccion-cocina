<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\DireccionEntregaCambiadaEvent;
use App\Application\Integration\CalendarProcessManager;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DireccionEntregaCambiadaHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly PaqueteRepositoryInterface $paqueteRepository,
        private readonly TransactionAggregate $transactionAggregate,
        private readonly CalendarProcessManager $calendarProcessManager
    ) {
    }

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

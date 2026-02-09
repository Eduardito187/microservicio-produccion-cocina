<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\RecetaActualizadaEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\RecetaVersion;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RecetaActualizadaHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly RecetaVersionRepositoryInterface $recetaVersionRepository,
        private readonly TransactionAggregate $transactionAggregate
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = RecetaActualizadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $existing = null;
            try {
                $existing = $this->recetaVersionRepository->byId($event->id);
            } catch (ModelNotFoundException $e) {
                $existing = null;
            }

            if ($existing === null && $event->nombre === null) {
                logger()->warning('Receta update ignored (missing nombre for create)', [
                    'receta_id' => $event->id,
                ]);
                return;
            }

            $receta = $existing ?? new RecetaVersion(
                $event->id,
                $event->nombre ?? '',
                $event->nutrientes,
                $event->ingredientes,
                $event->version ?? 1
            );

            if ($event->nombre !== null) {
                $receta->nombre = $event->nombre;
            }
            if ($event->nutrientes !== null) {
                $receta->nutrientes = $event->nutrientes;
            }
            if ($event->ingredientes !== null) {
                $receta->ingredientes = $event->ingredientes;
            }
            if ($event->version !== null) {
                $receta->version = $event->version;
            }

            $this->recetaVersionRepository->save($receta);
        });
    }
}

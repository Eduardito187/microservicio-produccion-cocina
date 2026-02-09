<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\PacienteActualizadoEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PacienteActualizadoHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly PacienteRepositoryInterface $pacienteRepository,
        private readonly TransactionAggregate $transactionAggregate
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = PacienteActualizadoEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $existing = null;
            try {
                $existing = $this->pacienteRepository->byId($event->id);
            } catch (ModelNotFoundException $e) {
                $existing = null;
            }

            if ($existing === null && $event->nombre === null) {
                logger()->warning('Paciente update ignored (missing nombre for create)', [
                    'paciente_id' => $event->id,
                ]);
                return;
            }

            $paciente = $existing ?? new Paciente(
                $event->id,
                $event->nombre ?? '',
                $event->documento,
                $event->suscripcionId
            );

            if ($event->nombre !== null) {
                $paciente->nombre = $event->nombre;
            }
            if ($event->documento !== null) {
                $paciente->documento = $event->documento;
            }
            if ($event->suscripcionId !== null) {
                $paciente->suscripcionId = $event->suscripcionId;
            }

            $this->pacienteRepository->save($paciente);
        });
    }
}

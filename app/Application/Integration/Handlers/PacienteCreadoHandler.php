<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\PacienteCreadoEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;

class PacienteCreadoHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly PacienteRepositoryInterface $pacienteRepository,
        private readonly TransactionAggregate $transactionAggregate
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = PacienteCreadoEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $paciente = new Paciente(
                $event->id,
                $event->nombre,
                $event->documento,
                $event->suscripcionId
            );
            $this->pacienteRepository->save($paciente);
        });
    }
}

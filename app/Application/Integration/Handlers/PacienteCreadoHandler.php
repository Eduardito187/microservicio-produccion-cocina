<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\PacienteCreadoEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;

/**
 * @class PacienteCreadoHandler
 */
class PacienteCreadoHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var PacienteRepositoryInterface
     */
    private $pacienteRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        PacienteRepositoryInterface $pacienteRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->pacienteRepository = $pacienteRepository;
        $this->transactionAggregate = $transactionAggregate;
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

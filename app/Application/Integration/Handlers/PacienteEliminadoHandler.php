<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\PacienteEliminadoEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class PacienteEliminadoHandler
 */
class PacienteEliminadoHandler implements IntegrationEventHandlerInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct(
        PacienteRepositoryInterface $pacienteRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->pacienteRepository = $pacienteRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = PacienteEliminadoEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $this->pacienteRepository->delete($event->id);
            $this->logger->info('Paciente eliminado por evento inbound', [
                'paciente_id' => $event->id,
            ]);
        });
    }
}

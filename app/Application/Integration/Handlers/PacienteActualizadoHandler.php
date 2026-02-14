<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Application\Integration\Events\PacienteActualizadoEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Produccion\Entity\Paciente;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class PacienteActualizadoHandler
 * @package App\Application\Integration\Handlers
 */
class PacienteActualizadoHandler implements IntegrationEventHandlerInterface
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
     *
     * @param PacienteRepositoryInterface $pacienteRepository
     * @param TransactionAggregate $transactionAggregate
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        PacienteRepositoryInterface $pacienteRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->pacienteRepository = $pacienteRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $event = PacienteActualizadoEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $existing = null;
            try {
                $existing = $this->pacienteRepository->byId($event->id);
            } catch (EntityNotFoundException $e) {
                $existing = null;
            }

            if ($existing === null && $event->nombre === null) {
                $this->logger->warning('Paciente update ignored (missing nombre for create)', [
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

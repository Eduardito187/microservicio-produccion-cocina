<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Application\Integration\Events\PacienteActualizadoEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Paciente;

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
     * Constructor
     *
     * @param PacienteRepositoryInterface $pacienteRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        PacienteRepositoryInterface $pacienteRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->pacienteRepository = $pacienteRepository;
        $this->transactionAggregate = $transactionAggregate;
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

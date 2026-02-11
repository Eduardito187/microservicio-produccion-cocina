<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarPaciente;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\PacienteActualizado;

/**
 * @class ActualizarPacienteHandler
 * @package App\Application\Produccion\Handler
 */
class ActualizarPacienteHandler
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
     * @var DomainEventPublisherInterface
     */
    private $eventPublisher;

    /**
     * Constructor
     *
     * @param PacienteRepositoryInterface $pacienteRepository
     * @param TransactionAggregate $transactionAggregate
     * @param DomainEventPublisherInterface $eventPublisher
     */
    public function __construct(
        PacienteRepositoryInterface $pacienteRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher
    ) {
        $this->pacienteRepository = $pacienteRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @param ActualizarPaciente $command
     * @return int
     */
    public function __invoke(ActualizarPaciente $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $paciente = $this->pacienteRepository->byId($command->id);
            $paciente->nombre = $command->nombre;
            $paciente->documento = $command->documento;
            $paciente->suscripcionId = $command->suscripcionId;

            $id = $this->pacienteRepository->save($paciente);
            $event = new PacienteActualizado($id, $paciente->nombre, $paciente->documento, $paciente->suscripcionId);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}

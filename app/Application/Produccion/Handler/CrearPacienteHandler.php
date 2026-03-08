<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\CrearPaciente;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Produccion\Events\PacienteCreado;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;

/**
 * @class CrearPacienteHandler
 */
class CrearPacienteHandler
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
     * @return int
     */
    public function __invoke(CrearPaciente $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $paciente = new Paciente(
                null,
                $command->nombre,
                $command->documento,
                $command->suscripcionId
            );

            $id = $this->pacienteRepository->save($paciente);
            $event = new PacienteCreado($id, $command->nombre, $command->documento, $command->suscripcionId);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}

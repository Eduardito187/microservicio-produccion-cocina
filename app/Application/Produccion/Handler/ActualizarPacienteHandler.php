<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarPaciente;

class ActualizarPacienteHandler
{
    /**
     * @var PacienteRepositoryInterface
     */
    public readonly PacienteRepositoryInterface $pacienteRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

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

            return $this->pacienteRepository->save($paciente);
        });
    }
}









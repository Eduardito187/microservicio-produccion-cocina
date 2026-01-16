<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearPaciente;
use App\Domain\Produccion\Entity\Paciente;

class CrearPacienteHandler
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
     * @param CrearPaciente $command
     * @return int
     */
    public function __invoke(CrearPaciente $command): int
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $paciente = new Paciente(
                null,
                $command->nombre,
                $command->documento,
                $command->suscripcionId
            );

            return $this->pacienteRepository->save($paciente);
        });
    }
}

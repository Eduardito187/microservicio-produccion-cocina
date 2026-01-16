<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarPaciente;

class EliminarPacienteHandler
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
     * @param EliminarPaciente $command
     * @return void
     */
    public function __invoke(EliminarPaciente $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->pacienteRepository->byId($command->id);
            $this->pacienteRepository->delete($command->id);
        });
    }
}

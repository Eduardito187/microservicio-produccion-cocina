<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarPaciente;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;

/**
 * @class EliminarPacienteHandler
 */
class EliminarPacienteHandler
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

    public function __invoke(EliminarPaciente $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->pacienteRepository->byId($command->id);
            $this->pacienteRepository->delete($command->id);
        });
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarPacientes;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;

/**
 * @class ListarPacientesHandler
 */
class ListarPacientesHandler
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

    public function __invoke(ListarPacientes $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapPaciente'], $this->pacienteRepository->list());
        });
    }

    private function mapPaciente(Paciente $paciente): array
    {
        return [
            'id' => $paciente->id,
            'nombre' => $paciente->nombre,
            'documento' => $paciente->documento,
            'suscripcion_id' => $paciente->suscripcionId,
        ];
    }
}

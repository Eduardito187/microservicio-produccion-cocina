<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarPacientesPorCalendario;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;

/**
 * @class ListarPacientesPorCalendarioHandler
 */
class ListarPacientesPorCalendarioHandler
{
    /**
     * @var PacienteRepositoryInterface
     */
    private $pacienteRepository;

    /**
     * @var CalendarioRepositoryInterface
     */
    private $calendarioRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    public function __construct(
        PacienteRepositoryInterface $pacienteRepository,
        CalendarioRepositoryInterface $calendarioRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->pacienteRepository = $pacienteRepository;
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarPacientesPorCalendario $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $this->calendarioRepository->byId($command->calendarioId);

            return array_map(
                [$this, 'mapPaciente'],
                $this->pacienteRepository->byCalendarioId($command->calendarioId)
            );
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

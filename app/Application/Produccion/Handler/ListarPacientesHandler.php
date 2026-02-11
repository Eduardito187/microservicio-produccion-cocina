<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ListarPacientes;
use App\Domain\Produccion\Entity\Paciente;

/**
 * @class ListarPacientesHandler
 * @package App\Application\Produccion\Handler
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
     * @param ListarPacientes $command
     * @return array
     */
    public function __invoke(ListarPacientes $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapPaciente'], $this->pacienteRepository->list());
        });
    }

    /**
     * @param Paciente $paciente
     * @return array
     */
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

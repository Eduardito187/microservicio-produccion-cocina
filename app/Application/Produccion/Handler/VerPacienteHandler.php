<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerPaciente;
use App\Domain\Produccion\Entity\Paciente;

/**
 * @class VerPacienteHandler
 * @package App\Application\Produccion\Handler
 */
class VerPacienteHandler
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
     * @param VerPaciente $command
     * @return array
     */
    public function __invoke(VerPaciente $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $paciente = $this->pacienteRepository->byId($command->id);
            return $this->mapPaciente($paciente);
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

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarPacientesPorVentanaEntrega;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;

/**
 * @class ListarPacientesPorVentanaEntregaHandler
 */
class ListarPacientesPorVentanaEntregaHandler
{
    /**
     * @var PacienteRepositoryInterface
     */
    private $pacienteRepository;

    /**
     * @var VentanaEntregaRepositoryInterface
     */
    private $ventanaEntregaRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    public function __construct(
        PacienteRepositoryInterface $pacienteRepository,
        VentanaEntregaRepositoryInterface $ventanaEntregaRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->pacienteRepository = $pacienteRepository;
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarPacientesPorVentanaEntrega $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $this->ventanaEntregaRepository->byId($command->ventanaEntregaId);

            return array_map(
                [$this, 'mapPaciente'],
                $this->pacienteRepository->byVentanaEntregaId($command->ventanaEntregaId)
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

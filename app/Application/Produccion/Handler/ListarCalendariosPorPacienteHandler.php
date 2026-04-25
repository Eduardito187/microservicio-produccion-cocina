<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarCalendariosPorPaciente;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;

/**
 * @class ListarCalendariosPorPacienteHandler
 */
class ListarCalendariosPorPacienteHandler
{
    /**
     * @var CalendarioRepositoryInterface
     */
    private $calendarioRepository;

    /**
     * @var PacienteRepositoryInterface
     */
    private $pacienteRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        PacienteRepositoryInterface $pacienteRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->pacienteRepository = $pacienteRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarCalendariosPorPaciente $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $this->pacienteRepository->byId($command->pacienteId);

            return array_map(
                [$this, 'mapCalendario'],
                $this->calendarioRepository->byPacienteId($command->pacienteId)
            );
        });
    }

    private function mapCalendario(Calendario $calendario): array
    {
        return [
            'id' => $calendario->id,
            'fecha' => $calendario->fecha->format('Y-m-d'),
            'entrega_id' => $calendario->entregaId,
            'contrato_id' => $calendario->contratoId,
            'estado' => $calendario->estado,
        ];
    }
}

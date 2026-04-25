<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarVentanasEntregaPorPaciente;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;

/**
 * @class ListarVentanasEntregaPorPacienteHandler
 */
class ListarVentanasEntregaPorPacienteHandler
{
    /**
     * @var VentanaEntregaRepositoryInterface
     */
    private $ventanaEntregaRepository;

    /**
     * @var PacienteRepositoryInterface
     */
    private $pacienteRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    public function __construct(
        VentanaEntregaRepositoryInterface $ventanaEntregaRepository,
        PacienteRepositoryInterface $pacienteRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
        $this->pacienteRepository = $pacienteRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarVentanasEntregaPorPaciente $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $this->pacienteRepository->byId($command->pacienteId);

            return array_map(
                [$this, 'mapVentana'],
                $this->ventanaEntregaRepository->byPacienteId($command->pacienteId)
            );
        });
    }

    private function mapVentana(VentanaEntrega $ventana): array
    {
        return [
            'id' => $ventana->id,
            'desde' => $ventana->desde->format('Y-m-d H:i:s'),
            'hasta' => $ventana->hasta->format('Y-m-d H:i:s'),
            'entrega_id' => $ventana->entregaId,
            'contrato_id' => $ventana->contratoId,
            'estado' => $ventana->estado,
        ];
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarCalendariosPorVentanaEntrega;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;

/**
 * @class ListarCalendariosPorVentanaEntregaHandler
 */
class ListarCalendariosPorVentanaEntregaHandler
{
    /**
     * @var CalendarioRepositoryInterface
     */
    private $calendarioRepository;

    /**
     * @var VentanaEntregaRepositoryInterface
     */
    private $ventanaEntregaRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        VentanaEntregaRepositoryInterface $ventanaEntregaRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarCalendariosPorVentanaEntrega $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $this->ventanaEntregaRepository->byId($command->ventanaEntregaId);

            return array_map(
                [$this, 'mapCalendario'],
                $this->calendarioRepository->byVentanaEntregaId($command->ventanaEntregaId)
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

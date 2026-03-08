<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ActualizarVentanaEntrega;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;

/**
 * @class ActualizarVentanaEntregaHandler
 */
class ActualizarVentanaEntregaHandler
{
    /**
     * @var VentanaEntregaRepositoryInterface
     */
    private $ventanaEntregaRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        VentanaEntregaRepositoryInterface $ventanaEntregaRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @return int
     */
    public function __invoke(ActualizarVentanaEntrega $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $ventana = $this->ventanaEntregaRepository->byId($command->id);
            $ventana->desde = $command->desde;
            $ventana->hasta = $command->hasta;

            return $this->ventanaEntregaRepository->save($ventana);
        });
    }
}

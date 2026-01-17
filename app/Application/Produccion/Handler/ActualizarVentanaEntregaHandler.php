<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarVentanaEntrega;

class ActualizarVentanaEntregaHandler
{
    /**
     * @var VentanaEntregaRepositoryInterface
     */
    public readonly VentanaEntregaRepositoryInterface $ventanaEntregaRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param VentanaEntregaRepositoryInterface $ventanaEntregaRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        VentanaEntregaRepositoryInterface $ventanaEntregaRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param ActualizarVentanaEntrega $command
     * @return int
     */
    public function __invoke(ActualizarVentanaEntrega $command): int
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $ventana = $this->ventanaEntregaRepository->byId($command->id);
            $ventana->desde = $command->desde;
            $ventana->hasta = $command->hasta;

            return $this->ventanaEntregaRepository->save($ventana);
        });
    }
}









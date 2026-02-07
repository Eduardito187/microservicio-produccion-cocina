<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearVentanaEntrega;
use App\Domain\Produccion\Entity\VentanaEntrega;

class CrearVentanaEntregaHandler
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
     * @param CrearVentanaEntrega $command
     * @return int
     */
    public function __invoke(CrearVentanaEntrega $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $ventana = new VentanaEntrega(
                null,
                $command->desde,
                $command->hasta
            );

            return $this->ventanaEntregaRepository->save($ventana);
        });
    }
}









<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarVentanaEntrega;

class EliminarVentanaEntregaHandler
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
     * @param EliminarVentanaEntrega $command
     * @return void
     */
    public function __invoke(EliminarVentanaEntrega $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->ventanaEntregaRepository->byId($command->id);
            $this->ventanaEntregaRepository->delete($command->id);
        });
    }
}

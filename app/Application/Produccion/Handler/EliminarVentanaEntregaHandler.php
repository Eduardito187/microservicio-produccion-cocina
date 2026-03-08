<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarVentanaEntrega;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;

/**
 * @class EliminarVentanaEntregaHandler
 */
class EliminarVentanaEntregaHandler
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

    public function __invoke(EliminarVentanaEntrega $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->ventanaEntregaRepository->byId($command->id);
            $this->ventanaEntregaRepository->delete($command->id);
        });
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Application\Produccion\Command\EliminarVentanaEntrega;
use App\Application\Support\Transaction\TransactionAggregate;

/**
 * @class EliminarVentanaEntregaHandler
 * @package App\Application\Produccion\Handler
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

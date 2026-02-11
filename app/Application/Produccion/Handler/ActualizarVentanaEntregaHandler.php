<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Application\Produccion\Command\ActualizarVentanaEntrega;
use App\Application\Support\Transaction\TransactionAggregate;

/**
 * @class ActualizarVentanaEntregaHandler
 * @package App\Application\Produccion\Handler
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

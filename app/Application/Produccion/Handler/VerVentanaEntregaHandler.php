<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerVentanaEntrega;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;

/**
 * @class VerVentanaEntregaHandler
 */
class VerVentanaEntregaHandler
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

    public function __invoke(VerVentanaEntrega $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $ventana = $this->ventanaEntregaRepository->byId($command->id);

            return $this->mapVentana($ventana);
        });
    }

    private function mapVentana(VentanaEntrega $ventana): array
    {
        return [
            'id' => $ventana->id,
            'desde' => $ventana->desde->format('Y-m-d H:i:s'),
            'hasta' => $ventana->hasta->format('Y-m-d H:i:s'),
        ];
    }
}

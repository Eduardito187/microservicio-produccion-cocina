<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarVentanasEntrega;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;

/**
 * @class ListarVentanasEntregaHandler
 */
class ListarVentanasEntregaHandler
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

    public function __invoke(ListarVentanasEntrega $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapVentana'], $this->ventanaEntregaRepository->listVigentes());
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

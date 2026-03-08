<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerDireccion;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Direccion;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;

/**
 * @class VerDireccionHandler
 */
class VerDireccionHandler
{
    /**
     * @var DireccionRepositoryInterface
     */
    private $direccionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(VerDireccion $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $direccion = $this->direccionRepository->byId($command->id);

            return $this->mapDireccion($direccion);
        });
    }

    private function mapDireccion(Direccion $direccion): array
    {
        return [
            'id' => $direccion->id,
            'nombre' => $direccion->nombre,
            'linea1' => $direccion->linea1,
            'linea2' => $direccion->linea2,
            'ciudad' => $direccion->ciudad,
            'provincia' => $direccion->provincia,
            'pais' => $direccion->pais,
            'geo' => $direccion->geo,
        ];
    }
}

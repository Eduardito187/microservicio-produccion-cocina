<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarDirecciones;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Direccion;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;

/**
 * @class ListarDireccionesHandler
 */
class ListarDireccionesHandler
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

    public function __invoke(ListarDirecciones $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapDireccion'], $this->direccionRepository->list());
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

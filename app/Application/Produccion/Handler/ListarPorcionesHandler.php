<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarPorciones;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Porcion;
use App\Domain\Produccion\Repository\PorcionRepositoryInterface;

/**
 * @class ListarPorcionesHandler
 */
class ListarPorcionesHandler
{
    /**
     * @var PorcionRepositoryInterface
     */
    private $porcionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        PorcionRepositoryInterface $porcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->porcionRepository = $porcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarPorciones $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapPorcion'], $this->porcionRepository->list());
        });
    }

    private function mapPorcion(Porcion $porcion): array
    {
        return [
            'id' => $porcion->id,
            'nombre' => $porcion->nombre,
            'peso_gr' => $porcion->pesoGr,
        ];
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PorcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ListarPorciones;
use App\Domain\Produccion\Entity\Porcion;

/**
 * @class ListarPorcionesHandler
 * @package App\Application\Produccion\Handler
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
     *
     * @param PorcionRepositoryInterface $porcionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        PorcionRepositoryInterface $porcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->porcionRepository = $porcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param ListarPorciones $command
     * @return array
     */
    public function __invoke(ListarPorciones $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapPorcion'], $this->porcionRepository->list());
        });
    }

    /**
     * @param Porcion $porcion
     * @return array
     */
    private function mapPorcion(Porcion $porcion): array
    {
        return [
            'id' => $porcion->id,
            'nombre' => $porcion->nombre,
            'peso_gr' => $porcion->pesoGr,
        ];
    }
}

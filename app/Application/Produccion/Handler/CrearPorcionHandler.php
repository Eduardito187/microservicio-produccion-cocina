<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\CrearPorcion;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Porcion;
use App\Domain\Produccion\Repository\PorcionRepositoryInterface;

/**
 * @class CrearPorcionHandler
 */
class CrearPorcionHandler
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

    /**
     * @return int
     */
    public function __invoke(CrearPorcion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $porcion = new Porcion(null, $command->nombre, $command->pesoGr);

            return $this->porcionRepository->save($porcion);
        });
    }
}

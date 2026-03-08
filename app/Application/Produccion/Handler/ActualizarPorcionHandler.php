<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ActualizarPorcion;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\PorcionRepositoryInterface;

/**
 * @class ActualizarPorcionHandler
 */
class ActualizarPorcionHandler
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
    public function __invoke(ActualizarPorcion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $porcion = $this->porcionRepository->byId($command->id);
            $porcion->nombre = $command->nombre;
            $porcion->pesoGr = $command->pesoGr;

            return $this->porcionRepository->save($porcion);
        });
    }
}

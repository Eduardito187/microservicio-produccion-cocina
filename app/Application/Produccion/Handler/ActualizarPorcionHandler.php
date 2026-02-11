<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PorcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarPorcion;

/**
 * @class ActualizarPorcionHandler
 * @package App\Application\Produccion\Handler
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
     * @param ActualizarPorcion $command
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

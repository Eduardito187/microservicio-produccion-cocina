<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EstacionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearEstacion;
use App\Domain\Produccion\Entity\Estacion;

/**
 * @class CrearEstacionHandler
 * @package App\Application\Produccion\Handler
 */
class CrearEstacionHandler
{
    /**
     * @var EstacionRepositoryInterface
     */
    private $estacionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     *
     * @param EstacionRepositoryInterface $estacionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        EstacionRepositoryInterface $estacionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->estacionRepository = $estacionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param CrearEstacion $command
     * @return int
     */
    public function __invoke(CrearEstacion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $estacion = new Estacion(null, $command->nombre, $command->capacidad);

            return $this->estacionRepository->save($estacion);
        });
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EstacionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarEstacion;

/**
 * @class ActualizarEstacionHandler
 * @package App\Application\Produccion\Handler
 */
class ActualizarEstacionHandler
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
     * @param ActualizarEstacion $command
     * @return int
     */
    public function __invoke(ActualizarEstacion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $estacion = $this->estacionRepository->byId($command->id);
            $estacion->nombre = $command->nombre;
            $estacion->capacidad = $command->capacidad;

            return $this->estacionRepository->save($estacion);
        });
    }
}

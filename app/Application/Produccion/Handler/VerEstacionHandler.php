<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EstacionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerEstacion;
use App\Domain\Produccion\Entity\Estacion;

/**
 * @class VerEstacionHandler
 * @package App\Application\Produccion\Handler
 */
class VerEstacionHandler
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
     * @param VerEstacion $command
     * @return array
     */
    public function __invoke(VerEstacion $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $estacion = $this->estacionRepository->byId($command->id);
            return $this->mapEstacion($estacion);
        });
    }

    /**
     * @param Estacion $estacion
     * @return array
     */
    private function mapEstacion(Estacion $estacion): array
    {
        return [
            'id' => $estacion->id,
            'nombre' => $estacion->nombre,
            'capacidad' => $estacion->capacidad,
        ];
    }
}

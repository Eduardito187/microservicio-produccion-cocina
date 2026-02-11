<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\EstacionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ListarEstaciones;
use App\Domain\Produccion\Entity\Estacion;

/**
 * @class ListarEstacionesHandler
 * @package App\Application\Produccion\Handler
 */
class ListarEstacionesHandler
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
     * @param ListarEstaciones $command
     * @return array
     */
    public function __invoke(ListarEstaciones $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapEstacion'], $this->estacionRepository->list());
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

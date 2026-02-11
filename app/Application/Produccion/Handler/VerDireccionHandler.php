<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerDireccion;
use App\Domain\Produccion\Entity\Direccion;

/**
 * @class VerDireccionHandler
 * @package App\Application\Produccion\Handler
 */
class VerDireccionHandler
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
     *
     * @param DireccionRepositoryInterface $direccionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param VerDireccion $command
     * @return array
     */
    public function __invoke(VerDireccion $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $direccion = $this->direccionRepository->byId($command->id);
            return $this->mapDireccion($direccion);
        });
    }

    /**
     * @param Direccion $direccion
     * @return array
     */
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

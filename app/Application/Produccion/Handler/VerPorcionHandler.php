<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PorcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerPorcion;
use App\Domain\Produccion\Entity\Porcion;

/**
 * @class VerPorcionHandler
 * @package App\Application\Produccion\Handler
 */
class VerPorcionHandler
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
     * @param VerPorcion $command
     * @return array
     */
    public function __invoke(VerPorcion $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $porcion = $this->porcionRepository->byId($command->id);
            return $this->mapPorcion($porcion);
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

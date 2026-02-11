<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerSuscripcion;
use App\Domain\Produccion\Entity\Suscripcion;

/**
 * @class VerSuscripcionHandler
 * @package App\Application\Produccion\Handler
 */
class VerSuscripcionHandler
{
    /**
     * @var SuscripcionRepositoryInterface
     */
    private $suscripcionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     *
     * @param SuscripcionRepositoryInterface $suscripcionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->suscripcionRepository = $suscripcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param VerSuscripcion $command
     * @return array
     */
    public function __invoke(VerSuscripcion $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $suscripcion = $this->suscripcionRepository->byId($command->id);
            return $this->mapSuscripcion($suscripcion);
        });
    }

    /**
     * @param Suscripcion $suscripcion
     * @return array
     */
    private function mapSuscripcion(Suscripcion $suscripcion): array
    {
        return [
            'id' => $suscripcion->id,
            'nombre' => $suscripcion->nombre,
        ];
    }
}

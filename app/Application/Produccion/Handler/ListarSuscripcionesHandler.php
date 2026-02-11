<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ListarSuscripciones;
use App\Domain\Produccion\Entity\Suscripcion;

/**
 * @class ListarSuscripcionesHandler
 * @package App\Application\Produccion\Handler
 */
class ListarSuscripcionesHandler
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
     * @param ListarSuscripciones $command
     * @return array
     */
    public function __invoke(ListarSuscripciones $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapSuscripcion'], $this->suscripcionRepository->list());
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

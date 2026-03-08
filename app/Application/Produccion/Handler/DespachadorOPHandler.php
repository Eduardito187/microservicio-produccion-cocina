<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\DespachadorOP;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;

/**
 * @class DespachadorOPHandler
 */
class DespachadorOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    private $ordenProduccionRepositoryInterface;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepositoryInterface,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ordenProduccionRepositoryInterface = $ordenProduccionRepositoryInterface;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(DespachadorOP $command): string|int|null
    {
        // etiqueta y paquete
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $ordenProduccion = $this->ordenProduccionRepositoryInterface->byId($command->ordenProduccionId);
            $ordenProduccion->generarItemsDespacho(
                $command->itemsDespacho,
                $command->pacienteId,
                $command->direccionId,
                $command->ventanaEntrega
            );
            $ordenProduccion->despacharBatches();
            $ordenProduccion->cerrar();

            return $this->ordenProduccionRepositoryInterface->save($ordenProduccion);
        });
    }
}

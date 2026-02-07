<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\DespachadorOP;

class DespachadorOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    public readonly OrdenProduccionRepositoryInterface $ordenProduccionRepositoryInterface;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $ordenProduccionRepositoryInterface
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepositoryInterface,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ordenProduccionRepositoryInterface = $ordenProduccionRepositoryInterface;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param DespachadorOP $command
     * @return string|int|null
     */
    public function __invoke(DespachadorOP $command): string|int|null
    {
        //etiqueta y paquete
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









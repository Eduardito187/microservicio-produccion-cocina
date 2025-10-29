<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\DespachadorOP;
use App\Domain\Produccion\ValueObjects\ItemDespacho;
use App\Domain\Produccion\Model\DespachoItems;
use DateTimeImmutable;

class DespachadorOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    public readonly OrdenProduccionRepositoryInterface $ordenProduccionRepositoryInterface;

    /**
     * @var ProduccionBatchRepositoryInterface
     */
    public readonly ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $ordenProduccionRepositoryInterface
     * @param ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepositoryInterface,
        ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ordenProduccionRepositoryInterface = $ordenProduccionRepositoryInterface;
        $this->produccionBatchRepositoryInterface = $produccionBatchRepositoryInterface;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param DespachadorOP $command
     * @return string|int|null
     */
    public function __invoke(DespachadorOP $command): string|int|null
    {
        //etiqueta y paquete
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $ordenProduccion = $this->ordenProduccionRepositoryInterface->byId($command->opId);
            $ordenProduccion->generarItemsDespacho();

            foreach ($ordenProduccion->batches() as $item) {
                $item->despachar();
                $this->produccionBatchRepositoryInterface->save($item);
            }

            $ordenProduccion->cerrar();
            return $this->ordenProduccionRepositoryInterface->save($ordenProduccion, true, true);
        });
    }
}
<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Aggregate\ListaDespacho as AggregateListaDespacho;
use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Infrastructure\Persistence\Repository\ListaDespachoRepository;
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
    public readonly OrdenProduccionRepositoryInterface $ordenProduccionRepository;

    /**
     * @var ProduccionBatchRepositoryInterface
     */
    public readonly ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface;

    /**
     * @var ListaDespachoRepository
     */
    public readonly ListaDespachoRepository $listaDespachoRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $ordenProduccionRepository
     * @param ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface
     * @param ListaDespachoRepository $listaDespachoRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepository,
        ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface,
        ListaDespachoRepository $listaDespachoRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ordenProduccionRepository = $ordenProduccionRepository;
        $this->produccionBatchRepositoryInterface = $produccionBatchRepositoryInterface;
        $this->listaDespachoRepository = $listaDespachoRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param DespachadorOP $command
     * @return string|int|null
     */
    public function __invoke(DespachadorOP $command): string|int|null
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $ordenProduccion = $this->ordenProduccionRepository->byId($command->opId);
            $listaDespacho = AggregateListaDespacho::crear($command->opId,  new DateTimeImmutable("now"),  $ordenProduccion->sucursalId());
            $itemsDespacho = [];

            foreach ($ordenProduccion->items() as $item) {
                $itemsDespacho[] = new ItemDespacho($listaDespacho->id, $item->sku(), 1, 1);
            }

            $itemsDespacho = DespachoItems::fromArray($itemsDespacho);
            $listaDespacho->replaceItems($itemsDespacho);
            $this->listaDespachoRepository->save($listaDespacho, true);

            foreach ($this->produccionBatchRepositoryInterface->byOrderId($command->opId) as $item) {
                $item->despachar();
                $this->produccionBatchRepositoryInterface->save($item);
            }

            $ordenProduccion->cerrar();
            return $this->ordenProduccionRepository->save($ordenProduccion, false, true);
        });
    }
}
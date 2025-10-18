<?php

namespace App\Application\Produccion\Handler;

use App\Infrastructure\Persistence\Eloquent\Repository\ListaDespachoRepository;
use App\Domain\Produccion\Aggregate\ListaDespacho as AggregateListaDespacho;
use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Outbox\OutboxStore;
use App\Application\Produccion\Command\DespachadorOP;
use App\Domain\Produccion\ValueObject\ItemDespacho;
use App\Domain\Produccion\Model\DespachoItems;
use Illuminate\Support\Facades\DB;
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
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $ordenProduccionRepository
     * @param ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface
     * @param ListaDespachoRepository $listaDespachoRepository
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepository,
        ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface,
        ListaDespachoRepository $listaDespachoRepository
    ) {
        $this->ordenProduccionRepository = $ordenProduccionRepository;
        $this->produccionBatchRepositoryInterface = $produccionBatchRepositoryInterface;
        $this->listaDespachoRepository = $listaDespachoRepository;
    }

    /**
     * @param DespachadorOP $command
     * @return string|int|null
     */
    public function __invoke(DespachadorOP $command): string|int|null
    {
        $ordenProduccionId = DB::transaction(function () use ($command): int {
            $ordenProduccion = $command->opId ? $this->ordenProduccionRepository->byId($command->opId) : null;

            if ($ordenProduccion == null) {
                //no existe la orden
            }

            $ordenProduccion->cerrar();
            $persistedId = $this->ordenProduccionRepository->save($ordenProduccion, false);

            $listaDespacho = AggregateListaDespacho::crear(
                null, 
                $command->opId, 
                new DateTimeImmutable("now"), 
                $ordenProduccion->sucursalId(),
                []
            );

            $itemsDespacho = [];

            foreach ($ordenProduccion->items() as $item) {
                $itemsDespacho[] = new ItemDespacho(
                    $listaDespacho->id,
                    $item->sku(),
                    1,
                    1,
                    [],
                    []
                );
            }

            $itemsDespacho = DespachoItems::fromArray($itemsDespacho);
            $listaDespacho->replaceItems($itemsDespacho);
            $this->listaDespachoRepository->save($listaDespacho, true);

            foreach ($this->produccionBatchRepositoryInterface->byOrderId($command->opId) as $item) {
                $item->desapchar();
                $this->produccionBatchRepositoryInterface->save($item);
            }

            foreach ($ordenProduccion->pullEvents() as $event) {
                OutboxStore::append(
                    name: $event->name(),
                    aggregateId: $persistedId,
                    occurredOn: $event->occurredOn(),
                    payload: $event->toArray()
                );
            }

            return $persistedId;
        });

        return $ordenProduccionId;
    }
}
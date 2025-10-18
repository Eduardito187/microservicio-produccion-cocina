<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Outbox\OutboxStore;
use App\Application\Produccion\Command\GenerarOP;
use App\Domain\Produccion\ValueObject\OrderItem;
use App\Domain\Produccion\Model\OrderItems;
use App\Domain\Produccion\ValueObject\Sku;
use App\Domain\Produccion\ValueObject\Qty;
use Illuminate\Support\Facades\DB;

class GenerarOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    public readonly OrdenProduccionRepositoryInterface $ordenProduccionRepository;

    /**
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $ordenProduccionRepository
     */
    public function __construct(OrdenProduccionRepositoryInterface $ordenProduccionRepository)
    {
        $this->ordenProduccionRepository = $ordenProduccionRepository;
    }

    /**
     * @param GenerarOP $command
     * @return string|int|null
     */
    public function __invoke(GenerarOP $command): string|int|null
    {
        $items = [];

        foreach ($command->items as $item) {
            $items[] = new OrderItem(
                new Sku($item['sku']),
                new Qty($item['qty'])
            );
        }

        $orderItems = OrderItems::fromArray($items);

        $ordenProduccionId = DB::transaction(function () use ($command, $orderItems): int {
            $ordenProduccion = $command->id
                ? $this->ordenProduccionRepository->byId($command->id)
                : AggregateOrdenProduccion::crear(null, $command->fecha, $command->sucursalId, $orderItems);

            $ordenProduccion->agregarItems($orderItems);
            $persistedId = $this->ordenProduccionRepository->save($ordenProduccion, true);

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
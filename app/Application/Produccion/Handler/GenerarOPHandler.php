<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Infrastructure\Persistence\Eloquent\Outbox\OutboxStore;
use App\Domain\Produccion\ValueObject\OrdenProduccion;
use App\Domain\Produccion\ValueObject\OrderItem;
use App\Domain\Produccion\Model\OrderItems;
use App\Domain\Produccion\ValueObject\Sku;
use App\Domain\Produccion\ValueObject\Qty;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GenerarOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    public readonly OrdenProduccionRepositoryInterface $repo;

    /**
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $repo
     */
    public function __construct(OrdenProduccionRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param OrdenProduccion $command
     * @return string|int|null
     */
    public function __invoke(OrdenProduccion $command): string|int|null
    {
        $domainItems = [];

        foreach ($command->items() as $it) {
            $domainItems[] = new OrderItem(
                new Sku($it['sku']),
                new Qty($it['qty'])
            );
        }

        $itemsCollection = OrderItems::fromArray($domainItems);

        $opId = DB::transaction(function () use ($command, $itemsCollection): int {
            $op = $command->id
                ? $this->repo->byId((int)$command->id)
                : null;

            if ($op === null) {
                $op = AggregateOrdenProduccion::crear(
                    null,
                    $command->fecha,
                    $command->sucursalId,
                    $itemsCollection
                );
            } else {
                $op->agregarItems($itemsCollection);
            }

            $persistedId = $this->repo->save($op);

            if ($persistedId === null) {
                throw new RuntimeException('OrdenProduccionRepository::save no asignó el id al agregado.');
            }

            foreach ($op->pullEvents() as $event) {
                OutboxStore::append(
                    name:        $event->name(),
                    aggregateId: (string)$persistedId,
                    occurredOn:  $event->occurredOn(),
                    payload:     $event->toArray()
                );
            }

            return (int)$persistedId;
        });

        return $opId;
    }
}
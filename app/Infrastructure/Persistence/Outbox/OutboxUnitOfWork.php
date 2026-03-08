<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Outbox;

use App\Application\Shared\OutboxStoreInterface;
use App\Application\Shared\OutboxUnitOfWorkInterface;
use App\Domain\Shared\Events\Interface\DomainEventInterface;

/**
 * @class OutboxUnitOfWork
 */
class OutboxUnitOfWork implements OutboxUnitOfWorkInterface
{
    /**
     * @var OutboxStoreInterface
     */
    private $outboxStore;

    /**
     * @var array<int, array{name:string, aggregateId:mixed, occurredOn:\DateTimeImmutable, payload:array}>
     */
    private array $pending = [];

    /**
     * Constructor
     */
    public function __construct(OutboxStoreInterface $outboxStore)
    {
        $this->outboxStore = $outboxStore;
    }

    /**
     * @param  DomainEventInterface[]  $events
     */
    public function register(array $events, mixed $aggregateId): void
    {
        foreach ($events as $event) {
            $this->pending[] = [
                'name' => $event->name(),
                'aggregateId' => $aggregateId ?? $event->aggregateId(),
                'occurredOn' => $event->occurredOn(),
                'payload' => $event->toArray(),
            ];
        }
    }

    public function flush(): void
    {
        if ($this->pending === []) {
            return;
        }

        foreach ($this->pending as $item) {
            $this->outboxStore->append(
                $item['name'],
                $item['aggregateId'],
                $item['occurredOn'],
                $item['payload']
            );
        }

        $this->clear();
    }

    public function clear(): void
    {
        $this->pending = [];
    }
}

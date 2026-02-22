<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Outbox;

use App\Domain\Shared\Events\Interface\DomainEventInterface;
use App\Application\Shared\OutboxUnitOfWorkInterface;
use App\Application\Shared\OutboxStoreInterface;

/**
 * @class OutboxUnitOfWork
 * @package App\Infrastructure\Persistence\Outbox
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
     *
     * @param OutboxStoreInterface $outboxStore
     */
    public function __construct(OutboxStoreInterface $outboxStore)
    {
        $this->outboxStore = $outboxStore;
    }

    /**
     * @param DomainEventInterface[] $events
     * @param mixed $aggregateId
     * @return void
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->pending = [];
    }
}


<?php

namespace App\Domain\Shared\Aggregate;

use App\Domain\Shared\Events\Interface\DomainEventInterface;
use App\Infrastructure\Persistence\Outbox\OutboxStore;

trait AggregateRoot
{
    /** 
     * @var DomainEventInterface[]
     */
    private array $events = [];

    /**
     * @param DomainEventInterface $event
     * @return void
     */
    protected function record(DomainEventInterface $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return DomainEventInterface[]
     */
    public function pullEvents(): array
    {
        $e = $this->events;
        $this->events = [];

        return $e;
    }

    /**
     * @return void
     */
    public function publishOutbox($persistenceId): void
    {
        foreach ($this->pullEvents() as $event) {
            OutboxStore::append(
                name: $event->name(),
                aggregateId: $persistenceId,
                occurredOn: $event->occurredOn(),
                payload: $event->toArray()
            );
        }
    }
}
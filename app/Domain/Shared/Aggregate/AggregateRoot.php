<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Shared\Aggregate;

use App\Domain\Shared\Events\Interface\DomainEventInterface;

/**
 * @trait AggregateRoot
 */
trait AggregateRoot
{
    /**
     * @var DomainEventInterface[]
     */
    private array $events = [];

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
}

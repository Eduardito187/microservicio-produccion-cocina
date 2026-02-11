<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Shared\Aggregate;

use App\Domain\Shared\Events\Interface\DomainEventInterface;
/**
 * @trait AggregateRoot
 * @package App\Domain\Shared\Aggregate
 */
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

}

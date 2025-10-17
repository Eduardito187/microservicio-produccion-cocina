<?php

namespace App\Domain\Shared;

trait AggregateRoot
{
    /** 
     * @var DomainEvent[]
     */
    private array $events = [];

    /**
     * @param DomainEvent $event
     * @return void
     */
    protected function record(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return DomainEvent[]
     */
    public function pullEvents(): array
    {
        $e = $this->events;
        $this->events = [];

        return $e;
    }
}
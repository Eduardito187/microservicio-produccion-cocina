<?php

namespace App\Domain\Shared;

trait AggregateRoot {
    /** 
     * @var DomainEvent[]
     */
    private array $events = [];

    /**
     * @param DomainEvent $e
     * @return void
     */
    protected function record(DomainEvent $e): void
    {
        $this->events[] = $e;
    
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
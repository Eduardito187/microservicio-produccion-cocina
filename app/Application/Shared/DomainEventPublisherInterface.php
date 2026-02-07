<?php

namespace App\Application\Shared;

use App\Domain\Shared\Events\Interface\DomainEventInterface;

interface DomainEventPublisherInterface
{
    /**
     * @param DomainEventInterface[] $events
     * @param mixed $aggregateId
     * @return void
     */
    public function publish(array $events, mixed $aggregateId): void;
}

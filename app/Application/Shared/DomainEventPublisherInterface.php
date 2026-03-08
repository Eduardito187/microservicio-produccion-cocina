<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Shared;

use App\Domain\Shared\Events\Interface\DomainEventInterface;

/**
 * @class DomainEventPublisherInterface
 */
interface DomainEventPublisherInterface
{
    /**
     * @param  DomainEventInterface[]  $events
     */
    public function publish(array $events, mixed $aggregateId): void;
}

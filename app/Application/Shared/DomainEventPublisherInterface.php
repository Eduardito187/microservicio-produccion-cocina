<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Shared;

use App\Domain\Shared\Events\Interface\DomainEventInterface;

/**
 * @class DomainEventPublisherInterface
 * @package App\Application\Shared
 */
interface DomainEventPublisherInterface
{
    /**
     * @param DomainEventInterface[] $events
     * @param mixed $aggregateId
     * @return void
     */
    public function publish(array $events, mixed $aggregateId): void;
}

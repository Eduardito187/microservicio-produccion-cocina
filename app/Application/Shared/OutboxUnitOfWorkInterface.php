<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Shared;

use App\Domain\Shared\Events\Interface\DomainEventInterface;

/**
 * @class OutboxUnitOfWorkInterface
 */
interface OutboxUnitOfWorkInterface
{
    /**
     * @param  DomainEventInterface[]  $events
     */
    public function register(array $events, mixed $aggregateId): void;

    public function flush(): void;

    public function clear(): void;
}

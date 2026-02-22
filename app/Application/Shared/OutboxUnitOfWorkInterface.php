<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Shared;

use App\Domain\Shared\Events\Interface\DomainEventInterface;

/**
 * @class OutboxUnitOfWorkInterface
 * @package App\Application\Shared
 */
interface OutboxUnitOfWorkInterface
{
    /**
     * @param DomainEventInterface[] $events
     * @param mixed $aggregateId
     * @return void
     */
    public function register(array $events, mixed $aggregateId): void;

    /**
     * @return void
     */
    public function flush(): void;

    /**
     * @return void
     */
    public function clear(): void;
}


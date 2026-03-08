<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Outbox;

use App\Application\Shared\OutboxStoreInterface;
use DateTimeImmutable;

/**
 * @class OutboxStoreAdapter
 */
class OutboxStoreAdapter implements OutboxStoreInterface
{
    public function append(string $name, string|int|null $aggregateId, DateTimeImmutable $occurredOn, array $payload): void
    {
        OutboxStore::append($name, $aggregateId, $occurredOn, $payload);
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Outbox;

use App\Application\Shared\OutboxStoreInterface;
use DateTimeImmutable;

/**
 * @class OutboxStoreAdapter
 * @package App\Infrastructure\Persistence\Outbox
 */
class OutboxStoreAdapter implements OutboxStoreInterface
{
    /**
     * @param string $name
     * @param string|int|null $aggregateId
     * @param DateTimeImmutable $occurredOn
     * @param array $payload
     * @return void
     */
    public function append(string $name, string|int|null $aggregateId, DateTimeImmutable $occurredOn, array $payload): void
    {
        OutboxStore::append($name, $aggregateId, $occurredOn, $payload);
    }
}

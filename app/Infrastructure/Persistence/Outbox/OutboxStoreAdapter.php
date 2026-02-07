<?php

namespace App\Infrastructure\Persistence\Outbox;

use App\Application\Shared\OutboxStoreInterface;
use DateTimeImmutable;

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

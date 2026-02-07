<?php

namespace App\Application\Shared;

use DateTimeImmutable;

interface OutboxStoreInterface
{
    /**
     * @param string $name
     * @param string|int|null $aggregateId
     * @param DateTimeImmutable $occurredOn
     * @param array $payload
     * @return void
     */
    public function append(string $name, string|int|null $aggregateId, DateTimeImmutable $occurredOn, array $payload): void;
}

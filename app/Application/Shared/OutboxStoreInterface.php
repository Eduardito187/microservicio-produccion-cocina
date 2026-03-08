<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Shared;

use DateTimeImmutable;

/**
 * @class OutboxStoreInterface
 */
interface OutboxStoreInterface
{
    public function append(string $name, string|int|null $aggregateId, DateTimeImmutable $occurredOn, array $payload): void;
}

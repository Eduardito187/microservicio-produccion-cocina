<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Shared;

use DateTimeImmutable;

/**
 * @class OutboxStoreInterface
 * @package App\Application\Shared
 */
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

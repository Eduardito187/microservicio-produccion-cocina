<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Shared\Events\Interface;

use DateTimeImmutable;

/**
 * @class DomainEventInterface
 */
interface DomainEventInterface
{
    public function name(): string;

    public function occurredOn(): DateTimeImmutable;

    public function aggregateId(): string|int|null;

    public function toArray(): array;
}

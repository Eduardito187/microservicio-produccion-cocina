<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Shared;

use DateTimeImmutable;

/**
 * @class BusInterface
 */
interface BusInterface
{
    public function publish(
        string $eventId,
        string $name,
        array $payload,
        DateTimeImmutable $occurredOn,
        array $meta = []
    ): void;
}

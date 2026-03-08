<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Shared;

use DateTimeImmutable;

/**
 * @class SimpleEventPublisher
 */
class SimpleEventPublisher
{
    /**
     * @var BusInterface
     */
    protected $bus;

    /**
     * Constructor
     */
    public function __construct(BusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function publish(string $eventId, string $name, array $payload): void
    {
        $this->bus->publish($eventId, $name, $payload, new DateTimeImmutable, []);
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Shared;

use DateTimeImmutable;

/**
 * @class SimpleEventPublisher
 * @package App\Application\Shared
 */
class SimpleEventPublisher
{
    /**
     * @var BusInterface
     */
    protected $bus;

    /**
     * Constructor
     *
     * @param BusInterface $bus
     */
    public function __construct(BusInterface $bus) {
        $this->bus = $bus;
    }

    /**
     * @param string $eventId
     * @param string $name
     * @param array $payload
     * @return void
     */
    public function publish(string $eventId, string $name, array $payload): void
    {
        $this->bus->publish($eventId, $name, $payload, new DateTimeImmutable(), []);
    }
}

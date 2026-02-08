<?php

namespace App\Application\Shared;

use DateTimeImmutable;

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

    public function publish(string $eventId, string $name, array $payload): void
    {
        $this->bus->publish($eventId, $name, $payload, new DateTimeImmutable(), []);
    }
}

<?php

namespace App\Application\Shared;

use DateTimeImmutable;

interface EventBusInterface
{
    /**
     * @param string $eventId
     * @param string $name
     * @param array $payload
     * @param DateTimeImmutable $occurredOn
     * @return void
     */
    public function publish(
        string $eventId,
        string $name,
        array $payload,
        DateTimeImmutable $occurredOn
    ): void;
}
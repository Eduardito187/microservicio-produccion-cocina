<?php

namespace App\Application\Shared;

use DateTimeImmutable;

interface EventBus {
    /**
     * @param string $name
     * @param array $payload
     * @param DateTimeImmutable $occurredOn
     * @return void
     */
    public function publish(string $name, array $payload, DateTimeImmutable $occurredOn): void;
}
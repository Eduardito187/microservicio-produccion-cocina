<?php

namespace App\Domain\Shared;

use DateTimeImmutable;

interface DomainEvent
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return DateTimeImmutable
     */
    public function occurredOn(): DateTimeImmutable;

    /**
     * @return string
     */
    public function aggregateId(): string;

    /**
     * @return array
     */
    public function toArray(): array;
}

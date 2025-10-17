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
     * @return string|int|null
     */
    public function aggregateId(): string|int|null;

    /**
     * @return array
     */
    public function toArray(): array;
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Shared\Events;

use App\Domain\Shared\Events\Interface\DomainEventInterface;
use DateTimeImmutable;

/**
 * @class BaseDomainEvent
 */
class BaseDomainEvent implements DomainEventInterface
{
    /**
     * @var string|int|null
     */
    protected $aggregateId;

    /**
     * @var ?DateTimeImmutable
     */
    protected $occurredOn;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $aggregateId,
        ?DateTimeImmutable $occurredOn = null
    ) {
        $this->aggregateId = $aggregateId;
        $this->occurredOn = $occurredOn ?? new DateTimeImmutable('now');
    }

    public function name(): string
    {
        return static::class;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function aggregateId(): string|int|null
    {
        return $this->aggregateId;
    }

    public function toArray(): array
    {
        return [];
    }
}

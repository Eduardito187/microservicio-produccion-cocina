<?php

namespace App\Domain\Shared;

use DateTimeImmutable;

class BaseDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    protected $aggregateId;

    /**
     * @var DateTimeImmutable
     */
    protected $occurredOn;

    /**
     * Constructor
     * 
     * @param string $aggregateId
     * @param mixed $occurredOn
     */
    public function __construct(
        string $aggregateId,
        ?DateTimeImmutable $occurredOn = null
    ) {
        $this->aggregateId = $aggregateId;
        $this->occurredOn  = $occurredOn ?? new DateTimeImmutable('now');
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return static::class;
    }

    /**
     * @return DateTimeImmutable
     */
    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * @return string
     */
    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }
}
<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Shared\Events;

use App\Domain\Shared\Events\Interface\DomainEventInterface;
use DateTimeImmutable;

/**
 * @class BaseDomainEvent
 * @package App\Domain\Shared\Events
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
     *
     * @param string|int|null $aggregateId
     * @param ?DateTimeImmutable $occurredOn
     */
    public function __construct(
        string|int|null $aggregateId,
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
     * @return string|int|null
     */
    public function aggregateId(): string|int|null
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

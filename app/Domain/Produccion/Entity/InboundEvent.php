<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class InboundEvent
 */
class InboundEvent
{
    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var string
     */
    public $eventId;

    /**
     * @var string
     */
    public $eventName;

    /**
     * @var string|null
     */
    public $occurredOn;

    /**
     * @var string
     */
    public $payload;

    /**
     * @var int|null
     */
    public $schemaVersion;

    /**
     * @var string|null
     */
    public $correlationId;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $id,
        string $eventId,
        string $eventName,
        ?string $occurredOn,
        string $payload,
        ?int $schemaVersion = null,
        ?string $correlationId = null
    ) {
        $this->id = $id;
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->occurredOn = $occurredOn;
        $this->payload = $payload;
        $this->schemaVersion = $schemaVersion;
        $this->correlationId = $correlationId;
    }
}

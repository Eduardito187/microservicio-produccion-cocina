<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class RegistrarInboundEvent
 */
class RegistrarInboundEvent
{
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
        string $eventId,
        string $eventName,
        ?string $occurredOn,
        string $payload,
        ?int $schemaVersion = null,
        ?string $correlationId = null
    ) {
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->occurredOn = $occurredOn;
        $this->payload = $payload;
        $this->schemaVersion = $schemaVersion;
        $this->correlationId = $correlationId;
    }
}

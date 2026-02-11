<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class RegistrarInboundEvent
 * @package App\Application\Produccion\Command
 */
class RegistrarInboundEvent
{
    /**
     * @var string
     */
    public string $eventId;

    /**
     * @var string
     */
    public string $eventName;

    /**
     * @var string|null
     */
    public string|null $occurredOn;

    /**
     * @var string
     */
    public string $payload;

    /**
     * @var int|null
     */
    public int|null $schemaVersion;

    /**
     * @var string|null
     */
    public string|null $correlationId;

    /**
     * Constructor
     *
     * @param string $eventId
     * @param string $eventName
     * @param string|null $occurredOn
     * @param string $payload
     * @param int|null $schemaVersion
     * @param string|null $correlationId
     */
    public function __construct(
        string $eventId,
        string $eventName,
        string|null $occurredOn,
        string $payload,
        int|null $schemaVersion = null,
        string|null $correlationId = null
    ) {
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->occurredOn = $occurredOn;
        $this->payload = $payload;
        $this->schemaVersion = $schemaVersion;
        $this->correlationId = $correlationId;
    }
}

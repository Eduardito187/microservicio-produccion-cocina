<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class InboundEvent
 * @package App\Domain\Produccion\Entity
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
     *
     * @param string|int|null $id
     * @param string $eventId
     * @param string $eventName
     * @param string|null $occurredOn
     * @param string $payload
     * @param int|null $schemaVersion
     * @param string|null $correlationId
     */
    public function __construct(
        string|int|null $id,
        string $eventId,
        string $eventName,
        string|null $occurredOn,
        string $payload,
        int|null $schemaVersion = null,
        string|null $correlationId = null
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

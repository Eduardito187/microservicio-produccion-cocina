<?php

namespace App\Application\Produccion\Command;

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
     * Constructor
     *
     * @param string $eventId
     * @param string $eventName
     * @param string|null $occurredOn
     * @param string $payload
     */
    public function __construct(
        string $eventId,
        string $eventName,
        string|null $occurredOn,
        string $payload
    ) {
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->occurredOn = $occurredOn;
        $this->payload = $payload;
    }
}

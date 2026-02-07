<?php

namespace App\Domain\Produccion\Entity;

class InboundEvent
{
    /**
     * @var string|int|null
     */
    public string|int|null $id;

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
     * @param string|int|null $id
     * @param string $eventId
     * @param string $eventName
     * @param string|null $occurredOn
     * @param string $payload
     */
    public function __construct(
        string|int|null $id,
        string $eventId,
        string $eventName,
        string|null $occurredOn,
        string $payload
    ) {
        $this->id = $id;
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->occurredOn = $occurredOn;
        $this->payload = $payload;
    }
}

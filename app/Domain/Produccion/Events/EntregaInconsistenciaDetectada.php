<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class EntregaInconsistenciaDetectada
 */
class EntregaInconsistenciaDetectada extends BaseDomainEvent
{
    /**
     * @var string
     */
    private $reason;

    /**
     * @var ?string
     */
    private $eventId;

    /**
     * @var ?string
     */
    private $packageId;

    /**
     * @var array
     */
    private $payload;

    public function __construct(?string $opId, string $reason, ?string $eventId, ?string $packageId, array $payload = [])
    {
        parent::__construct($opId);
        $this->reason = $reason;
        $this->eventId = $eventId;
        $this->packageId = $packageId;
        $this->payload = $payload;
    }

    public function toArray(): array
    {
        return [
            'id' => (string) ($this->aggregateId() ?? ''),
            'ordenProduccionId' => $this->aggregateId(),
            'eventId' => $this->eventId,
            'packageId' => $this->packageId,
            'reason' => $this->reason,
            'payload' => $this->payload,
        ];
    }
}

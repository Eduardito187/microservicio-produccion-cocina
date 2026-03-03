<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class EntregaInconsistenciaDetectada
 * @package App\Domain\Produccion\Events
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

    /**
     * @param ?string $opId
     * @param string $reason
     * @param ?string $eventId
     * @param ?string $packageId
     * @param array $payload
     */
    public function __construct(?string $opId, string $reason, ?string $eventId, ?string $packageId, array $payload = [])
    {
        parent::__construct($opId);
        $this->reason = $reason;
        $this->eventId = $eventId;
        $this->packageId = $packageId;
        $this->payload = $payload;
    }

    /**
     * @return array
     */
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

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarEstadoPaqueteDesdeLogisticaCommand
 */
class ActualizarEstadoPaqueteDesdeLogisticaCommand
{
    /**
     * @var string
     */
    public $eventId;

    /**
     * @var string
     */
    public $packageId;

    /**
     * @var string
     */
    public $deliveryStatus;

    /**
     * @var ?string
     */
    public $occurredOn;

    /**
     * @var ?string
     */
    public $driverId;

    /**
     * @var mixed
     */
    public $deliveryEvidence;

    /**
     * @var array
     */
    public $payload;

    public function __construct(
        string $eventId,
        string $packageId,
        string $deliveryStatus,
        ?string $occurredOn,
        ?string $driverId,
        mixed $deliveryEvidence,
        array $payload
    ) {
        $this->eventId = $eventId;
        $this->packageId = $packageId;
        $this->deliveryStatus = $deliveryStatus;
        $this->occurredOn = $occurredOn;
        $this->driverId = $driverId;
        $this->deliveryEvidence = $deliveryEvidence;
        $this->payload = $payload;
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarEstadoPaqueteDesdeLogisticaCommand
 * @package App\Application\Produccion\Command
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

    /**
     * @param string $eventId
     * @param string $packageId
     * @param string $deliveryStatus
     * @param ?string $occurredOn
     * @param ?string $driverId
     * @param mixed $deliveryEvidence
     * @param array $payload
     */
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

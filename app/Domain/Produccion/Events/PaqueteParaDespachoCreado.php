<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class PaqueteParaDespachoCreado
 */
class PaqueteParaDespachoCreado extends BaseDomainEvent
{
    /**
     * @var string
     */
    private $number;

    /**
     * @var string|int
     */
    private $patientId;

    /**
     * @var string
     */
    private $patientName;

    /**
     * @var string
     */
    private $deliveryAddress;

    /**
     * @var float
     */
    private $deliveryLatitude;

    /**
     * @var float
     */
    private $deliveryLongitude;

    /**
     * @var string
     */
    private $deliveryDate;

    /**
     * Constructor
     */
    public function __construct(
        string|int $paqueteId,
        string $number,
        string|int $patientId,
        string $patientName,
        string $deliveryAddress,
        float $deliveryLatitude,
        float $deliveryLongitude,
        string $deliveryDate
    ) {
        parent::__construct($paqueteId);
        $this->number = $number;
        $this->patientId = $patientId;
        $this->patientName = $patientName;
        $this->deliveryAddress = $deliveryAddress;
        $this->deliveryLatitude = $deliveryLatitude;
        $this->deliveryLongitude = $deliveryLongitude;
        $this->deliveryDate = $deliveryDate;
    }

    public function toArray(): array
    {
        return [
            'id' => (string) $this->aggregateId(),
            'number' => $this->number,
            'patientId' => (string) $this->patientId,
            'patientName' => $this->patientName,
            'deliveryAddress' => $this->deliveryAddress,
            'deliveryLatitude' => $this->deliveryLatitude,
            'deliveryLongitude' => $this->deliveryLongitude,
            'deliveryDate' => $this->deliveryDate,
        ];
    }
}

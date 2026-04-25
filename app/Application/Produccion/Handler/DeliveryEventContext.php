<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\ValueObjects\DriverId;
use App\Domain\Produccion\ValueObjects\OccurredOn;
use App\Domain\Produccion\ValueObjects\PackageStatus;

/**
 * Transporta el contexto del evento de entrega a través de los métodos privados del handler.
 *
 * @class DeliveryEventContext
 */
class DeliveryEventContext
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $packageId,
        public readonly PackageStatus $nextStatus,
        public readonly ?OccurredOn $occurredOn,
        public readonly ?DriverId $driverId,
        public readonly array $payload,
    ) {}
}

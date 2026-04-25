<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

/**
 * Transporta los datos necesarios para actualizar el registro de tracking de un paquete.
 *
 * @class TrackingUpdateContext
 */
class TrackingUpdateContext
{
    public function __construct(
        public readonly string $packageId,
        public readonly DeliveryContextIds $context,
        public readonly ?string $driverId,
        public readonly ?string $status,
        public readonly bool $statusLocked,
        public readonly ?string $completedAt,
        public readonly TrackingEventRef $event,
    ) {}
}

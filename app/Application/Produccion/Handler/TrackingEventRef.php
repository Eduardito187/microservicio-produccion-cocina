<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

/**
 * Referencia al evento logístico que originó la actualización de tracking.
 *
 * @class TrackingEventRef
 */
class TrackingEventRef
{
    public function __construct(
        public readonly string $eventId,
        public readonly ?string $occurredAt,
    ) {}
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

/**
 * Estado mutable compartido durante el procesamiento del bucle de filas de despacho.
 *
 * @class RowProcessingState
 */
class RowProcessingState
{
    public bool $trackingUpserted = false;

    public bool $missingOpAlertRaised = false;

    public bool $packageCompletedMetricCounted = false;
}

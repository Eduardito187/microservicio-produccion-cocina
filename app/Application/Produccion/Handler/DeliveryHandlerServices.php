<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Service\DeliveryContextBackfiller;
use App\Application\Produccion\Service\DeliveryStatusMapper;
use App\Application\Produccion\Service\OrderDeliveryProgressSync;

/**
 * Agrupa los servicios de aplicación que necesita ActualizarEstadoPaqueteDesdeLogisticaHandler.
 *
 * @class DeliveryHandlerServices
 */
class DeliveryHandlerServices
{
    public function __construct(
        public readonly DeliveryStatusMapper $statusMapper,
        public readonly DeliveryContextBackfiller $backfiller,
        public readonly OrderDeliveryProgressSync $progressSync,
    ) {}
}

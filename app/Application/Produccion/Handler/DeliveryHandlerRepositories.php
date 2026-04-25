<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Application\Produccion\Repository\DeliveryInconsistencyQueueRepositoryInterface;
use App\Application\Produccion\Repository\OrderDeliveryProgressRepositoryInterface;
use App\Application\Produccion\Repository\PackageDeliveryHistoryRepositoryInterface;
use App\Application\Produccion\Repository\PackageDeliveryTrackingRepositoryInterface;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;

/**
 * Agrupa los repositorios que necesita ActualizarEstadoPaqueteDesdeLogisticaHandler
 * para mantener el constructor dentro del límite de parámetros.
 *
 * @class DeliveryHandlerRepositories
 */
class DeliveryHandlerRepositories
{
    public function __construct(
        public readonly EntregaEvidenciaRepositoryInterface $evidencia,
        public readonly PackageDeliveryHistoryRepositoryInterface $history,
        public readonly PackageDeliveryTrackingRepositoryInterface $tracking,
        public readonly OrderDeliveryProgressRepositoryInterface $progress,
        public readonly DeliveryInconsistencyQueueRepositoryInterface $inconsistency,
        public readonly ItemDespachoRepositoryInterface $itemDespacho,
        public readonly OrdenProduccionRepositoryInterface $ordenProduccion,
        public readonly VentanaEntregaRepositoryInterface $ventanaEntrega,
    ) {}
}

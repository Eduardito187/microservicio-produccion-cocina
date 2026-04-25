<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Repository\OrderDeliveryProgressRepositoryInterface;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;

/**
 * Agrupa los repositorios usados durante el cierre de una entrega.
 *
 * @class DeliveryCompletionRepos
 */
class DeliveryCompletionRepos
{
    public function __construct(
        public readonly OrderDeliveryProgressRepositoryInterface $progress,
        public readonly OrdenProduccionRepositoryInterface $ordenProduccion,
        public readonly VentanaEntregaRepositoryInterface $ventanaEntrega,
    ) {}
}

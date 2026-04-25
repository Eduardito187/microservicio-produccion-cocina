<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Repository;

/**
 * @interface OrdenProduccionQueryRepositoryInterface
 */
interface OrdenProduccionQueryRepositoryInterface
{
    public function consolidadoPorSuscripcion(string $suscripcionId): ?array;

    public function porId(string $opId): ?array;

    public function todos(): array;
}

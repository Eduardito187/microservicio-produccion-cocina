<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Analytics;

/**
 * @class KpiRepositoryInterface
 */
interface KpiRepositoryInterface
{
    public function increment(string $name, int $by = 1): void;
}

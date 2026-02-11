<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Analytics;

/**
 * @class KpiRepositoryInterface
 * @package App\Application\Analytics
 */
interface KpiRepositoryInterface
{
    /**
     * @param string $name
     * @param int $by
     * @return void
     */
    public function increment(string $name, int $by = 1): void;
}

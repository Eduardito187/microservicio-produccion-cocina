<?php

namespace App\Application\Analytics;

interface KpiRepositoryInterface
{
    /**
     * @param string $name
     * @param int $by
     * @return void
     */
    public function increment(string $name, int $by = 1): void;
}

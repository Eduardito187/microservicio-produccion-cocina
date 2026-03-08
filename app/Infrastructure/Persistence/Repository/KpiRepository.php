<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Analytics\KpiRepositoryInterface;
use App\Infrastructure\Persistence\Model\KpiOperativo;

/**
 * @class KpiRepository
 */
class KpiRepository implements KpiRepositoryInterface
{
    public function increment(string $name, int $by = 1): void
    {
        $row = KpiOperativo::query()->firstOrCreate(
            ['name' => $name],
            ['value' => 0]
        );

        $row->value += $by;
        $row->save();
    }
}

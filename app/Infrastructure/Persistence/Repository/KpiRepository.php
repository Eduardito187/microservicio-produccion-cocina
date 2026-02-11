<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\KpiOperativo;
use App\Application\Analytics\KpiRepositoryInterface;

/**
 * @class KpiRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class KpiRepository implements KpiRepositoryInterface
{
    /**
     * @param string $name
     * @param int $by
     * @return void
     */
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

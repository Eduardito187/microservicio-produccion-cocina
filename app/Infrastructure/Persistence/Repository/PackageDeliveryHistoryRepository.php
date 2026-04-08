<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Produccion\Repository\PackageDeliveryHistoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * @class PackageDeliveryHistoryRepository
 */
class PackageDeliveryHistoryRepository implements PackageDeliveryHistoryRepositoryInterface
{
    public function findByEventId(string $eventId): ?object
    {
        return DB::table('package_delivery_history')
            ->where('event_id', $eventId)
            ->first();
    }

    public function insert(array $data): void
    {
        DB::table('package_delivery_history')->insert($data);
    }

    public function updateByEventId(string $eventId, array $data): void
    {
        DB::table('package_delivery_history')
            ->where('event_id', $eventId)
            ->update($data);
    }
}

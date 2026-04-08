<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Produccion\Repository\PackageDeliveryTrackingRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * @class PackageDeliveryTrackingRepository
 */
class PackageDeliveryTrackingRepository implements PackageDeliveryTrackingRepositoryInterface
{
    public function findByPackageId(string $packageId): ?object
    {
        return DB::table('package_delivery_tracking')
            ->where('package_id', $packageId)
            ->first();
    }

    public function upsertByPackageId(string $packageId, array $values): void
    {
        DB::table('package_delivery_tracking')->updateOrInsert(
            ['package_id' => $packageId],
            $values
        );
    }
}

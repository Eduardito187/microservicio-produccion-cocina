<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Repository;

/**
 * @class PackageDeliveryTrackingRepositoryInterface
 */
interface PackageDeliveryTrackingRepositoryInterface
{
    public function findByPackageId(string $packageId): ?object;

    public function upsertByPackageId(string $packageId, array $values): void;
}

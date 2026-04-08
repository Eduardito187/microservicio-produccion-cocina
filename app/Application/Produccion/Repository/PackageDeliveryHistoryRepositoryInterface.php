<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Repository;

/**
 * @class PackageDeliveryHistoryRepositoryInterface
 */
interface PackageDeliveryHistoryRepositoryInterface
{
    public function findByEventId(string $eventId): ?object;

    public function insert(array $data): void;

    public function updateByEventId(string $eventId, array $data): void;
}

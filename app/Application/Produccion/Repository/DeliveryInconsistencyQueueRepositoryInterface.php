<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Repository;

/**
 * @class DeliveryInconsistencyQueueRepositoryInterface
 */
interface DeliveryInconsistencyQueueRepositoryInterface
{
    public function existsByEventIdAndReason(string $eventId, string $reason): bool;

    public function insert(array $data): void;

    public function updateByEventIdAndReason(string $eventId, string $reason, array $data): void;
}

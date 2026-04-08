<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Produccion\Repository\DeliveryInconsistencyQueueRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * @class DeliveryInconsistencyQueueRepository
 */
class DeliveryInconsistencyQueueRepository implements DeliveryInconsistencyQueueRepositoryInterface
{
    public function existsByEventIdAndReason(string $eventId, string $reason): bool
    {
        return DB::table('delivery_inconsistency_queue')
            ->where('event_id', $eventId)
            ->where('reason', $reason)
            ->exists();
    }

    public function insert(array $data): void
    {
        DB::table('delivery_inconsistency_queue')->insert($data);
    }

    public function updateByEventIdAndReason(string $eventId, string $reason, array $data): void
    {
        DB::table('delivery_inconsistency_queue')
            ->where('event_id', $eventId)
            ->where('reason', $reason)
            ->update($data);
    }
}

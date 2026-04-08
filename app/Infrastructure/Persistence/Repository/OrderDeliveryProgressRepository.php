<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Produccion\Repository\OrderDeliveryProgressRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * @class OrderDeliveryProgressRepository
 */
class OrderDeliveryProgressRepository implements OrderDeliveryProgressRepositoryInterface
{
    public function findByOpId(string $opId): ?object
    {
        return DB::table('order_delivery_progress')
            ->where('op_id', $opId)
            ->first();
    }

    public function upsertByOpId(string $opId, array $values): void
    {
        DB::table('order_delivery_progress')->updateOrInsert(
            ['op_id' => $opId],
            $values
        );
    }

    public function markCompletionIfNotSet(string $opId, string $completionEventId, string $allCompletedAt): int
    {
        return DB::table('order_delivery_progress')
            ->where('op_id', $opId)
            ->whereNull('completion_event_id')
            ->update([
                'completion_event_id' => $completionEventId,
                'all_completed_at' => $allCompletedAt,
                'updated_at' => now(),
            ]);
    }
}

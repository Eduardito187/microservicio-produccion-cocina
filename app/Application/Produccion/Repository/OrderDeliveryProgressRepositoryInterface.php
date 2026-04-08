<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Repository;

/**
 * @class OrderDeliveryProgressRepositoryInterface
 */
interface OrderDeliveryProgressRepositoryInterface
{
    public function findByOpId(string $opId): ?object;

    public function upsertByOpId(string $opId, array $values): void;

    /**
     * Marca el progreso como completado solo si aun no tiene completion_event_id.
     *
     * @return int filas actualizadas (0 = ya estaba marcado)
     */
    public function markCompletionIfNotSet(string $opId, string $completionEventId, string $allCompletedAt): int;
}

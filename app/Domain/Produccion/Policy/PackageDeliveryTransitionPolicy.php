<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Policy;

/**
 * @class PackageDeliveryTransitionPolicy
 */
class PackageDeliveryTransitionPolicy
{
    public static function canTransition(?string $currentStatus, string $nextStatus): bool
    {
        $current = self::normalize($currentStatus);
        $next = self::normalize($nextStatus);

        if ($current !== 'confirmada') {
            return true;
        }

        // Completed is terminal: once confirmed, only repeated confirmed status is accepted as no-op.
        return $next === 'confirmada';
    }

    public static function isCompleted(?string $status): bool
    {
        return self::normalize($status) === 'confirmada';
    }

    private static function normalize(?string $status): ?string
    {
        if (! is_string($status) || trim($status) === '') {
            return null;
        }

        return strtolower(trim($status));
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Policy;

/**
 * @class PackageDeliveryTransitionPolicy
 * @package App\Domain\Produccion\Policy
 */
class PackageDeliveryTransitionPolicy
{
    /**
     * @param ?string $currentStatus
     * @param string $nextStatus
     * @return bool
     */
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

    /**
     * @param ?string $status
     * @return bool
     */
    public static function isCompleted(?string $status): bool
    {
        return self::normalize($status) === 'confirmada';
    }

    /**
     * @param ?string $status
     * @return ?string
     */
    private static function normalize(?string $status): ?string
    {
        if (!is_string($status) || trim($status) === '') {
            return null;
        }

        return strtolower(trim($status));
    }
}

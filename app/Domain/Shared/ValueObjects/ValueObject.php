<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Shared\ValueObjects;

/**
 * @class ValueObject
 */
class ValueObject
{
    public function equals(self $other): bool
    {
        return $this == $other;
    }
}

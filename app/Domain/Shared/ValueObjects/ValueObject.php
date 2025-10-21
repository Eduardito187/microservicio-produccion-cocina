<?php

namespace App\Domain\Shared\ValueObjects;

class ValueObject
{
    /**
     * @param ValueObject $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this == $other;
    }
}
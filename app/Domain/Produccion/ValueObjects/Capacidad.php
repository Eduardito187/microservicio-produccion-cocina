<?php

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObject;
use InvalidArgumentException;

class Capacidad extends ValueObject
{
    /** @var string */
    public readonly string $value;

    /**
     * Constructor
     * 
     * @param int $value
     * @throws InvalidArgumentException
     */
    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Capacidad > 0');
        }

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }
}
<?php

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

class Capacidad extends ValueObject
{
    /** @var string */
    public readonly string $value;

    /**
     * Constructor
     * 
     * @param int $value
     * @throws DomainException
     */
    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new DomainException('Capacidad > 0');
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
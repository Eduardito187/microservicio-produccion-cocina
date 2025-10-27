<?php

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

class Sku extends ValueObject
{
    /** @var string */
    public readonly string $value;

    /**
     * Constructor
     * 
     * @param string $value
     * @throws DomainException
     */
    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new DomainException('SKU cannot be empty');
        }

        $this->value = strtoupper($value);
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }
}
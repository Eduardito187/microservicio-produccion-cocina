<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

/**
 * @class Sku
 */
class Sku extends ValueObject
{
    /**
     * @var string
     */
    public $value;

    /**
     * Constructor
     */
    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new DomainException('SKU cannot be empty');
        }

        $this->value = strtoupper($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}

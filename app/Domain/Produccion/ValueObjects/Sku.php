<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

/**
 * @class Sku
 * @package App\Domain\Produccion\ValueObjects
 */
class Sku extends ValueObject
{
    /** 
     * @var string
     */
    public $value;

    /**
     * Constructor
     *
     * @param string $value
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

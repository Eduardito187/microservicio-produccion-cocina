<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

/**
 * @class DriverId
 * @package App\Domain\Produccion\ValueObjects
 */
class DriverId extends ValueObject
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === '') {
            throw new DomainException('DriverId cannot be empty');
        }

        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $normalized)) {
            throw new DomainException('DriverId must be UUID');
        }

        $this->value = strtolower($normalized);
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }
}

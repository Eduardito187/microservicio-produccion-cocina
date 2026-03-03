<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

/**
 * @class ContratoId
 * @package App\Domain\Produccion\ValueObjects
 */
class ContratoId extends ValueObject
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
            throw new DomainException('ContratoId cannot be empty');
        }

        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $normalized)) {
            throw new DomainException('ContratoId must be UUID');
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

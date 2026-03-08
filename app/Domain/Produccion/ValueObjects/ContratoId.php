<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

/**
 * @class ContratoId
 */
class ContratoId extends ValueObject
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === '') {
            throw new DomainException('ContratoId cannot be empty');
        }

        if (! preg_match('/^[0-9a-fA-F-]{36}$/', $normalized)) {
            throw new DomainException('ContratoId must be UUID');
        }

        $this->value = strtolower($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }
}

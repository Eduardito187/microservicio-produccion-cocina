<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DateTimeImmutable;

/**
 * @class OccurredOn
 */
class OccurredOn extends ValueObject
{
    /**
     * @var DateTimeImmutable
     */
    private $value;

    public function __construct(string|DateTimeImmutable $value)
    {
        $this->value = $value instanceof DateTimeImmutable ? $value : new DateTimeImmutable($value);
    }

    public function value(): DateTimeImmutable
    {
        return $this->value;
    }

    public function toDatabase(): string
    {
        return $this->value->format('Y-m-d H:i:s');
    }
}

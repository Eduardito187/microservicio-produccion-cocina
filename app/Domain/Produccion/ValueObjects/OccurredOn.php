<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DateTimeImmutable;

/**
 * @class OccurredOn
 * @package App\Domain\Produccion\ValueObjects
 */
class OccurredOn extends ValueObject
{
    /**
     * @var DateTimeImmutable
     */
    private $value;

    /**
     * @param string|DateTimeImmutable $value
     */
    public function __construct(string|DateTimeImmutable $value)
    {
        $this->value = $value instanceof DateTimeImmutable ? $value : new DateTimeImmutable($value);
    }

    /**
     * @return DateTimeImmutable
     */
    public function value(): DateTimeImmutable
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function toDatabase(): string
    {
        return $this->value->format('Y-m-d H:i:s');
    }
}

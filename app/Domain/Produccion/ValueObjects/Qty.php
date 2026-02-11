<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

/**
 * @class Qty
 * @package App\Domain\Produccion\ValueObjects
 */
class Qty extends ValueObject
{
    /**
     * @var int
     */
    public $value;

    /**
     * Constructor
     *
     * @param int $value
     */
    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new DomainException('Qty > 0');
        }

        $this->value = $value;
    }

    /**
     * @return int
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * @param Qty $other
     * @return Qty
     */
    public function add(Qty $other): self
    {
        return new self($this->value + $other->value());
    }
}

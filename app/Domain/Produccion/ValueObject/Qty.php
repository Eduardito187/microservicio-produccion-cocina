<?php

namespace App\Domain\Produccion\ValueObject;

use App\Domain\Shared\ValueObject;
use InvalidArgumentException;

class Qty extends ValueObject
{
    /**
     * @var int
     */
    public readonly int $value;

    /**
     * Constructor
     * 
     * @param int $value
     * @throws InvalidArgumentException
     */
    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Qty > 0');
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
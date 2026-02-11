<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

/**
 * @class Capacidad
 * @package App\Domain\Produccion\ValueObjects
 */
class Capacidad extends ValueObject
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
            throw new DomainException('Capacidad > 0');
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
}

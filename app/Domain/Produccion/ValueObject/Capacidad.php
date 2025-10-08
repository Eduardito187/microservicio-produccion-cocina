<?php

namespace App\Domain\Produccion\ValueObject;

use App\Domain\Shared\ValueObject;
use InvalidArgumentException;

class Capacidad extends ValueObject {
    /**
     * Constructor
     * 
     * @param int $valor
     * @throws InvalidArgumentException
     */
    public function __construct(public int $valor)
    {
        if ($valor <= 0) throw new InvalidArgumentException('Capacidad > 0');
    }
}
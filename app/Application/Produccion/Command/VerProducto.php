<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class VerProducto
 */
class VerProducto
{
    /**
     * @var string
     */
    public $id;

    /**
     * Constructor
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }
}

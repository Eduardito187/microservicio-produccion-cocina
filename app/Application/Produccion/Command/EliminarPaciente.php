<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class EliminarPaciente
 */
class EliminarPaciente
{
    /**
     * @var istringnt
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

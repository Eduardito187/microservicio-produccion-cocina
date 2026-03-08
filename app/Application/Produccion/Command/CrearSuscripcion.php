<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearSuscripcion
 */
class CrearSuscripcion
{
    /**
     * @var string
     */
    public $nombre;

    /**
     * Constructor
     */
    public function __construct(string $nombre)
    {
        $this->nombre = $nombre;
    }
}

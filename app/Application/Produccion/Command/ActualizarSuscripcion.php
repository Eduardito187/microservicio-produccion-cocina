<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarSuscripcion
 */
class ActualizarSuscripcion
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $nombre;

    /**
     * Constructor
     */
    public function __construct(string $id, string $nombre)
    {
        $this->id = $id;
        $this->nombre = $nombre;
    }
}

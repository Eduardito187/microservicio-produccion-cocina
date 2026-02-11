<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearSuscripcion
 * @package App\Application\Produccion\Command
 */
class CrearSuscripcion
{
    /**
     * @var string
     */
    public $nombre;

    /**
     * Constructor
     *
     * @param string $nombre
     */
    public function __construct(string $nombre)
    {
        $this->nombre = $nombre;
    }
}

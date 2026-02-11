<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearEstacion
 * @package App\Application\Produccion\Command
 */
class CrearEstacion
{
    /**
     * @var string
     */
    public $nombre;

    /**
     * @var int|null
     */
    public $capacidad;

    /**
     * Constructor
     *
     * @param string $nombre
     * @param int|null $capacidad
     */
    public function __construct(string $nombre, int|null $capacidad = null)
    {
        $this->nombre = $nombre;
        $this->capacidad = $capacidad;
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarEstacion
 * @package App\Application\Produccion\Command
 */
class ActualizarEstacion
{
    /**
     * @var int
     */
    public string $id;

    /**
     * @var string
     */
    public string $nombre;

    /**
     * @var int|null
     */
    public int|null $capacidad;

    /**
     * Constructor
     *
     * @param string $id
     * @param string $nombre
     * @param int|null $capacidad
     */
    public function __construct(string $id, string $nombre, int|null $capacidad = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->capacidad = $capacidad;
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarSuscripcion
 * @package App\Application\Produccion\Command
 */
class ActualizarSuscripcion
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
     * Constructor
     *
     * @param string $id
     * @param string $nombre
     */
    public function __construct(string $id, string $nombre)
    {
        $this->id = $id;
        $this->nombre = $nombre;
    }
}

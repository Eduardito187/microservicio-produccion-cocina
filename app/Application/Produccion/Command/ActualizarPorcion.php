<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarPorcion
 * @package App\Application\Produccion\Command
 */
class ActualizarPorcion
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
     * @var int
     */
    public $pesoGr;

    /**
     * Constructor
     *
     * @param string $id
     * @param string $nombre
     * @param int $pesoGr
     */
    public function __construct(string $id, string $nombre, int $pesoGr)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pesoGr = $pesoGr;
    }
}

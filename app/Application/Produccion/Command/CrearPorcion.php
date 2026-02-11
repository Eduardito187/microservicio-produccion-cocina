<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearPorcion
 * @package App\Application\Produccion\Command
 */
class CrearPorcion
{
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
     * @param string $nombre
     * @param int $pesoGr
     */
    public function __construct(string $nombre, int $pesoGr)
    {
        $this->nombre = $nombre;
        $this->pesoGr = $pesoGr;
    }
}

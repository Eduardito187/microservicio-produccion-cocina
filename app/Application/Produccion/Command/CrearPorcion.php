<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearPorcion
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
     */
    public function __construct(string $nombre, int $pesoGr)
    {
        $this->nombre = $nombre;
        $this->pesoGr = $pesoGr;
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarPorcion
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
     */
    public function __construct(string $id, string $nombre, int $pesoGr)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pesoGr = $pesoGr;
    }
}

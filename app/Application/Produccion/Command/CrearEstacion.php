<?php

namespace App\Application\Produccion\Command;

class CrearEstacion
{
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
     * @param string $nombre
     * @param int|null $capacidad
     */
    public function __construct(string $nombre, int|null $capacidad = null)
    {
        $this->nombre = $nombre;
        $this->capacidad = $capacidad;
    }
}




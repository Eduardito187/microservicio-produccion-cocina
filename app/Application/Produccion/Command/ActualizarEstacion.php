<?php

namespace App\Application\Produccion\Command;

class ActualizarEstacion
{
    /**
     * @var int
     */
    public int $id;

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
     * @param int $id
     * @param string $nombre
     * @param int|null $capacidad
     */
    public function __construct(int $id, string $nombre, int|null $capacidad = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->capacidad = $capacidad;
    }
}




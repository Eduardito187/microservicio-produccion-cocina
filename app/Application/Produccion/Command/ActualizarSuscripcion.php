<?php

namespace App\Application\Produccion\Command;

class ActualizarSuscripcion
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
     * Constructor
     *
     * @param int $id
     * @param string $nombre
     */
    public function __construct(int $id, string $nombre)
    {
        $this->id = $id;
        $this->nombre = $nombre;
    }
}




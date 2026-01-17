<?php

namespace App\Application\Produccion\Command;

class CrearSuscripcion
{
    /**
     * @var string
     */
    public string $nombre;

    /**
     * Constructor
     *
     * @param string $nombre
     */
    public function __construct(string $nombre)
    {
        $this->nombre = $nombre;
    }
}




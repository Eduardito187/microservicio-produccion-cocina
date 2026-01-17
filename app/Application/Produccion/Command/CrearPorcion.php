<?php

namespace App\Application\Produccion\Command;

class CrearPorcion
{
    /**
     * @var string
     */
    public string $nombre;

    /**
     * @var int
     */
    public int $pesoGr;

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




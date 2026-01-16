<?php

namespace App\Application\Produccion\Command;

class ActualizarPorcion
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
     * @var int
     */
    public int $pesoGr;

    /**
     * Constructor
     *
     * @param int $id
     * @param string $nombre
     * @param int $pesoGr
     */
    public function __construct(int $id, string $nombre, int $pesoGr)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pesoGr = $pesoGr;
    }
}

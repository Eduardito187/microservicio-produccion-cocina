<?php

namespace App\Domain\Produccion\Entity;

class Porcion
{
    /**
     * @var int|null
     */
    public int|null $id;

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
     * @param int|null $id
     * @param string $nombre
     * @param int $pesoGr
     */
    public function __construct(int|null $id, string $nombre, int $pesoGr)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pesoGr = $pesoGr;
    }
}

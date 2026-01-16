<?php

namespace App\Domain\Produccion\Entity;

class Suscripcion
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
     * Constructor
     *
     * @param int|null $id
     * @param string $nombre
     */
    public function __construct(int|null $id, string $nombre)
    {
        $this->id = $id;
        $this->nombre = $nombre;
    }
}

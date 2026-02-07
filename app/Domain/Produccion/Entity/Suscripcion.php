<?php

namespace App\Domain\Produccion\Entity;

class Suscripcion
{
    /**
     * @var string|int|null
     */
    public string|int|null $id;

    /**
     * @var string
     */
    public string $nombre;

    /**
     * Constructor
     *
     * @param string|int|null $id
     * @param string $nombre
     */
    public function __construct(string|int|null $id, string $nombre)
    {
        $this->id = $id;
        $this->nombre = $nombre;
    }
}

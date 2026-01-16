<?php

namespace App\Domain\Produccion\Entity;

class RecetaVersion
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
     * @var array|null
     */
    public array|null $nutrientes;

    /**
     * @var array|null
     */
    public array|null $ingredientes;

    /**
     * @var int
     */
    public int $version;

    /**
     * Constructor
     *
     * @param int|null $id
     * @param string $nombre
     * @param array|null $nutrientes
     * @param array|null $ingredientes
     * @param int $version
     */
    public function __construct(
        int|null $id,
        string $nombre,
        array|null $nutrientes = null,
        array|null $ingredientes = null,
        int $version = 1
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->nutrientes = $nutrientes;
        $this->ingredientes = $ingredientes;
        $this->version = $version;
    }
}

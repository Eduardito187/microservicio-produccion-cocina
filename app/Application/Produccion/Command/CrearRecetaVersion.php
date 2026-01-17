<?php

namespace App\Application\Produccion\Command;

class CrearRecetaVersion
{
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
     * @param string $nombre
     * @param array|null $nutrientes
     * @param array|null $ingredientes
     * @param int $version
     */
    public function __construct(
        string $nombre,
        array|null $nutrientes = null,
        array|null $ingredientes = null,
        int $version = 1
    ) {
        $this->nombre = $nombre;
        $this->nutrientes = $nutrientes;
        $this->ingredientes = $ingredientes;
        $this->version = $version;
    }
}




<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearRecetaVersion
 * @package App\Application\Produccion\Command
 */
class CrearRecetaVersion
{
    /**
     * @var string
     */
    public $nombre;

    /**
     * @var array|null
     */
    public $nutrientes;

    /**
     * @var array|null
     */
    public $ingredientes;

    /**
     * @var int
     */
    public $version;

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

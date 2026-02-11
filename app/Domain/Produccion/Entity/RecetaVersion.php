<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class RecetaVersion
 * @package App\Domain\Produccion\Entity
 */
class RecetaVersion
{
    /**
     * @var string|int|null
     */
    public $id;

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
     * @param string|int|null $id
     * @param string $nombre
     * @param array|null $nutrientes
     * @param array|null $ingredientes
     * @param int $version
     */
    public function __construct(
        string|int|null $id,
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

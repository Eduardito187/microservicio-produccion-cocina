<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Estacion
 * @package App\Domain\Produccion\Entity
 */
class Estacion
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
     * @var int|null
     */
    public $capacidad;

    /**
     * Constructor
     *
     * @param string|int|null $id
     * @param string $nombre
     * @param int|null $capacidad
     */
    public function __construct(string|int|null $id, string $nombre, int|null $capacidad = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->capacidad = $capacidad;
    }
}

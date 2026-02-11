<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Suscripcion
 * @package App\Domain\Produccion\Entity
 */
class Suscripcion
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

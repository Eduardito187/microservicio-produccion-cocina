<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Porcion
 */
class Porcion
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
     * @var int
     */
    public $pesoGr;

    /**
     * Constructor
     */
    public function __construct(string|int|null $id, string $nombre, int $pesoGr)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pesoGr = $pesoGr;
    }
}

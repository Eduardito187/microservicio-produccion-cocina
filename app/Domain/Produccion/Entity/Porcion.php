<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Porcion
 * @package App\Domain\Produccion\Entity
 */
class Porcion
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
     * @var int
     */
    public int $pesoGr;

    /**
     * Constructor
     *
     * @param string|int|null $id
     * @param string $nombre
     * @param int $pesoGr
     */
    public function __construct(string|int|null $id, string $nombre, int $pesoGr)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pesoGr = $pesoGr;
    }
}

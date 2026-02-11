<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Direccion
 * @package App\Domain\Produccion\Entity
 */
class Direccion
{
    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $nombre;

    /**
     * @var string
     */
    public $linea1;

    /**
     * @var string|null
     */
    public $linea2;

    /**
     * @var string|null
     */
    public $ciudad;

    /**
     * @var string|null
     */
    public $provincia;

    /**
     * @var string|null
     */
    public $pais;

    /**
     * @var array|null
     */
    public $geo;

    /**
     * Constructor
     *
     * @param string|int|null $id
     * @param string|null $nombre
     * @param string $linea1
     * @param string|null $linea2
     * @param string|null $ciudad
     * @param string|null $provincia
     * @param string|null $pais
     * @param array|null $geo
     */
    public function __construct(
        string|int|null $id,
        string|null $nombre,
        string $linea1,
        string|null $linea2 = null,
        string|null $ciudad = null,
        string|null $provincia = null,
        string|null $pais = null,
        array|null $geo = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->linea1 = $linea1;
        $this->linea2 = $linea2;
        $this->ciudad = $ciudad;
        $this->provincia = $provincia;
        $this->pais = $pais;
        $this->geo = $geo;
    }
}

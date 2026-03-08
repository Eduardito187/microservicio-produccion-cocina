<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarDireccion
 */
class ActualizarDireccion
{
    /**
     * @var int
     */
    public string $id;

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
     */
    public function __construct(
        string $id,
        ?string $nombre,
        string $linea1,
        ?string $linea2 = null,
        ?string $ciudad = null,
        ?string $provincia = null,
        ?string $pais = null,
        ?array $geo = null
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

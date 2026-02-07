<?php

namespace App\Application\Produccion\Command;

class ActualizarDireccion
{
    /**
     * @var int
     */
    public string $id;

    /**
     * @var string|null
     */
    public string|null $nombre;

    /**
     * @var string
     */
    public string $linea1;

    /**
     * @var string|null
     */
    public string|null $linea2;

    /**
     * @var string|null
     */
    public string|null $ciudad;

    /**
     * @var string|null
     */
    public string|null $provincia;

    /**
     * @var string|null
     */
    public string|null $pais;

    /**
     * @var array|null
     */
    public array|null $geo;

    /**
     * Constructor
     *
     * @param string $id
     * @param string|null $nombre
     * @param string $linea1
     * @param string|null $linea2
     * @param string|null $ciudad
     * @param string|null $provincia
     * @param string|null $pais
     * @param array|null $geo
     */
    public function __construct(
        string $id,
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




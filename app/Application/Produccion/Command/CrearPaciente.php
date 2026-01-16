<?php

namespace App\Application\Produccion\Command;

class CrearPaciente
{
    /**
     * @var string
     */
    public string $nombre;

    /**
     * @var string|null
     */
    public string|null $documento;

    /**
     * @var int|null
     */
    public int|null $suscripcionId;

    /**
     * Constructor
     *
     * @param string $nombre
     * @param string|null $documento
     * @param int|null $suscripcionId
     */
    public function __construct(
        string $nombre,
        string|null $documento = null,
        int|null $suscripcionId = null
    ) {
        $this->nombre = $nombre;
        $this->documento = $documento;
        $this->suscripcionId = $suscripcionId;
    }
}

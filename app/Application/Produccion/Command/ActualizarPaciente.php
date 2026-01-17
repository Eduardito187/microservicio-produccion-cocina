<?php

namespace App\Application\Produccion\Command;

class ActualizarPaciente
{
    /**
     * @var int
     */
    public int $id;

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
     * @param int $id
     * @param string $nombre
     * @param string|null $documento
     * @param int|null $suscripcionId
     */
    public function __construct(
        int $id,
        string $nombre,
        string|null $documento = null,
        int|null $suscripcionId = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->documento = $documento;
        $this->suscripcionId = $suscripcionId;
    }
}




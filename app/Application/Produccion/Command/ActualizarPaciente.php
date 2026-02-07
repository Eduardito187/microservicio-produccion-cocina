<?php

namespace App\Application\Produccion\Command;

class ActualizarPaciente
{
    /**
     * @var string|int
     */
    public string|int $id;

    /**
     * @var string
     */
    public string $nombre;

    /**
     * @var string|null
     */
    public string|null $documento;

    /**
     * @var string|int|null
     */
    public string|int|null $suscripcionId;

    /**
     * Constructor
     *
     * @param string|int $id
     * @param string $nombre
     * @param string|null $documento
     * @param string|int|null $suscripcionId
     */
    public function __construct(
        string|int $id,
        string $nombre,
        string|null $documento = null,
        string|int|null $suscripcionId = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->documento = $documento;
        $this->suscripcionId = $suscripcionId;
    }
}




<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearPaciente
 */
class CrearPaciente
{
    /**
     * @var string
     */
    public $nombre;

    /**
     * @var string|null
     */
    public $documento;

    /**
     * @var string|int|null
     */
    public $suscripcionId;

    /**
     * Constructor
     */
    public function __construct(
        string $nombre,
        ?string $documento = null,
        string|int|null $suscripcionId = null
    ) {
        $this->nombre = $nombre;
        $this->documento = $documento;
        $this->suscripcionId = $suscripcionId;
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearPaciente
 * @package App\Application\Produccion\Command
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
     *
     * @param string $nombre
     * @param string|null $documento
     * @param string|int|null $suscripcionId
     */
    public function __construct(
        string $nombre,
        string|null $documento = null,
        string|int|null $suscripcionId = null
    ) {
        $this->nombre = $nombre;
        $this->documento = $documento;
        $this->suscripcionId = $suscripcionId;
    }
}

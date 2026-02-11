<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarPaciente
 * @package App\Application\Produccion\Command
 */
class ActualizarPaciente
{
    /**
     * @var string|int
     */
    public $id;

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

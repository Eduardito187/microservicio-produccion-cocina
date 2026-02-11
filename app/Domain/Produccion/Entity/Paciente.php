<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Paciente
 * @package App\Domain\Produccion\Entity
 */
class Paciente
{
    /**
     * @var string|int|null
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
     * @param string|int|null $id
     * @param string $nombre
     * @param string|null $documento
     * @param string|int|null $suscripcionId
     */
    public function __construct(
        string|int|null $id,
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

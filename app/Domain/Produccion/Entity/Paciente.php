<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Paciente
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
     */
    public function __construct(
        string|int|null $id,
        string $nombre,
        ?string $documento = null,
        string|int|null $suscripcionId = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->documento = $documento;
        $this->suscripcionId = $suscripcionId;
    }
}

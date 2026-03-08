<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarEtiqueta
 */
class ActualizarEtiqueta
{
    /**
     * @var string|int
     */
    public $id;

    /**
     * @var string|int|null
     */
    public $suscripcionId;

    /**
     * @var string|int|null
     */
    public $pacienteId;

    /**
     * @var array|null
     */
    public $qrPayload;

    /**
     * Constructor
     */
    public function __construct(
        string|int $id,
        string|int|null $suscripcionId,
        string|int|null $pacienteId,
        ?array $qrPayload = null
    ) {
        $this->id = $id;
        $this->suscripcionId = $suscripcionId;
        $this->pacienteId = $pacienteId;
        $this->qrPayload = $qrPayload;
    }
}

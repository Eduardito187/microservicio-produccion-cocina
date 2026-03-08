<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearEtiqueta
 */
class CrearEtiqueta
{
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
        string|int|null $suscripcionId,
        string|int|null $pacienteId,
        ?array $qrPayload = null
    ) {
        $this->suscripcionId = $suscripcionId;
        $this->pacienteId = $pacienteId;
        $this->qrPayload = $qrPayload;
    }
}

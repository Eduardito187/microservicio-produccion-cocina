<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class CrearEtiqueta
 * @package App\Application\Produccion\Command
 */
class CrearEtiqueta
{
    /**
     * @var string|int|null
     */
    public $recetaVersionId;

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
     *
     * @param string|int|null $recetaVersionId
     * @param string|int|null $suscripcionId
     * @param string|int|null $pacienteId
     * @param array|null $qrPayload
     */
    public function __construct(
        string|int|null $recetaVersionId,
        string|int|null $suscripcionId,
        string|int|null $pacienteId,
        array|null $qrPayload = null
    ) {
        $this->recetaVersionId = $recetaVersionId;
        $this->suscripcionId = $suscripcionId;
        $this->pacienteId = $pacienteId;
        $this->qrPayload = $qrPayload;
    }
}

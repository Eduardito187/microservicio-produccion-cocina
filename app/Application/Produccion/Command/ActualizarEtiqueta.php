<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarEtiqueta
 * @package App\Application\Produccion\Command
 */
class ActualizarEtiqueta
{
    /**
     * @var string|int
     */
    public string|int $id;

    /**
     * @var string|int|null
     */
    public string|int|null $recetaVersionId;

    /**
     * @var string|int|null
     */
    public string|int|null $suscripcionId;

    /**
     * @var string|int|null
     */
    public string|int|null $pacienteId;

    /**
     * @var array|null
     */
    public array|null $qrPayload;

    /**
     * Constructor
     *
     * @param string|int $id
     * @param string|int|null $recetaVersionId
     * @param string|int|null $suscripcionId
     * @param string|int|null $pacienteId
     * @param array|null $qrPayload
     */
    public function __construct(
        string|int $id,
        string|int|null $recetaVersionId,
        string|int|null $suscripcionId,
        string|int|null $pacienteId,
        array|null $qrPayload = null
    ) {
        $this->id = $id;
        $this->recetaVersionId = $recetaVersionId;
        $this->suscripcionId = $suscripcionId;
        $this->pacienteId = $pacienteId;
        $this->qrPayload = $qrPayload;
    }
}

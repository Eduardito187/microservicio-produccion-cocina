<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Etiqueta
 * @package App\Domain\Produccion\Entity
 */
class Etiqueta
{
    /**
     * @var string|int|null
     */
    public $id;

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
     * @param string|int|null $id
     * @param string|int|null $recetaVersionId
     * @param string|int|null $suscripcionId
     * @param string|int|null $pacienteId
     * @param array|null $qrPayload
     */
    public function __construct(
        string|int|null $id,
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

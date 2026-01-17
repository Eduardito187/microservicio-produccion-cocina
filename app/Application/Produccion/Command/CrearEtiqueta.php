<?php

namespace App\Application\Produccion\Command;

class CrearEtiqueta
{
    /**
     * @var int|null
     */
    public int|null $recetaVersionId;

    /**
     * @var int|null
     */
    public int|null $suscripcionId;

    /**
     * @var int|null
     */
    public int|null $pacienteId;

    /**
     * @var array|null
     */
    public array|null $qrPayload;

    /**
     * Constructor
     *
     * @param int|null $recetaVersionId
     * @param int|null $suscripcionId
     * @param int|null $pacienteId
     * @param array|null $qrPayload
     */
    public function __construct(
        int|null $recetaVersionId,
        int|null $suscripcionId,
        int|null $pacienteId,
        array|null $qrPayload = null
    ) {
        $this->recetaVersionId = $recetaVersionId;
        $this->suscripcionId = $suscripcionId;
        $this->pacienteId = $pacienteId;
        $this->qrPayload = $qrPayload;
    }
}




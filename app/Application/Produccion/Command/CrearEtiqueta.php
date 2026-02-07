<?php

namespace App\Application\Produccion\Command;

class CrearEtiqueta
{
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




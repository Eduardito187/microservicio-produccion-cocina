<?php

namespace App\Domain\Produccion\Entity;

class Etiqueta
{
    /**
     * @var int|null
     */
    public int|null $id;

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
     * @param int|null $id
     * @param int|null $recetaVersionId
     * @param int|null $suscripcionId
     * @param int|null $pacienteId
     * @param array|null $qrPayload
     */
    public function __construct(
        int|null $id,
        int|null $recetaVersionId,
        int|null $suscripcionId,
        int|null $pacienteId,
        array|null $qrPayload = null
    ) {
        $this->id = $id;
        $this->recetaVersionId = $recetaVersionId;
        $this->suscripcionId = $suscripcionId;
        $this->pacienteId = $pacienteId;
        $this->qrPayload = $qrPayload;
    }
}

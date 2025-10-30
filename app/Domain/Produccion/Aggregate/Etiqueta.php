<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Shared\Aggregate\AggregateRoot;

class Etiqueta
{
    use AggregateRoot;

    /**
     * @var int|null
     */
    private int|null $id;

    /**
     * @var int
     */
    private int $recetaVersionId;

    /**
     * @var int
     */
    private int $suscripcionId;

    /**
     * @var int
     */
    private int $pacienteId;

    /**
     * @var array
     */
    private array $qrPayload;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param int $recetaVersionId
     * @param int $suscripcionId
     * @param int $pacienteId
     * @param array $qrPayload
     */
    public function __construct(
        int|null $id,
        int $recetaVersionId,
        int $suscripcionId,
        int $pacienteId,
        array $qrPayload = []
    ) {
        $this->id = $id;
        $this->recetaVersionId = $recetaVersionId;
        $this->suscripcionId = $suscripcionId;
        $this->pacienteId = $pacienteId;
        $this->qrPayload = $qrPayload;
    }

    /**
     * @param int|null $id
     * @param int $recetaVersionId
     * @param int $suscripcionId
     * @param int $pacienteId
     * @param array $qrPayload
     * @return Etiqueta
     */
    public static function crear(
        int|null $id,
        int $recetaVersionId,
        int $suscripcionId,
        int $pacienteId,
        array $qrPayload = []
    ): self {
        $self = new self(
            $id,
            $recetaVersionId,
            $suscripcionId,
            $pacienteId,
            $qrPayload
        );

        //$self->record();

        return $self;
    }

    /**
     * @param int $id
     * @param int $recetaVersionId
     * @param int $suscripcionId
     * @param int $pacienteId
     * @param array $qrPayload
     * @return Etiqueta
     */
    public static function reconstitute(
        int $id,
        int $recetaVersionId,
        int $suscripcionId,
        int $pacienteId,
        array $qrPayload
    ): self {
        $self = new self(
            $id,
            $recetaVersionId,
            $suscripcionId,
            $pacienteId,
            $qrPayload
        );

        return $self;
    }
}
<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Shared\Aggregate\AggregateRoot;

class Etiqueta
{
    use AggregateRoot;

    /**
     * @var string|int|null
     */
    private string|int|null $id;

    /**
     * @var int
     */
    private string|int $recetaVersionId;

    /**
     * @var int
     */
    private string|int $suscripcionId;

    /**
     * @var int
     */
    private string|int $pacienteId;

    /**
     * @var array
     */
    private array $qrPayload;

    /**
     * Constructor
     * 
     * @param string|int|null $id
     * @param string|int $recetaVersionId
     * @param string|int $suscripcionId
     * @param string|int $pacienteId
     * @param array $qrPayload
     */
    public function __construct(
        string|int|null $id,
        string|int $recetaVersionId,
        string|int $suscripcionId,
        string|int $pacienteId,
        array $qrPayload = []
    ) {
        $this->id = $id;
        $this->recetaVersionId = $recetaVersionId;
        $this->suscripcionId = $suscripcionId;
        $this->pacienteId = $pacienteId;
        $this->qrPayload = $qrPayload;
    }

    /**
     * @param string|int|null $id
     * @param string|int $recetaVersionId
     * @param string|int $suscripcionId
     * @param string|int $pacienteId
     * @param array $qrPayload
     * @return Etiqueta
     */
    public static function crear(
        string|int|null $id,
        string|int $recetaVersionId,
        string|int $suscripcionId,
        string|int $pacienteId,
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
     * @param string|int $recetaVersionId
     * @param string|int $suscripcionId
     * @param string|int $pacienteId
     * @param array $qrPayload
     * @return Etiqueta
     */
    public static function reconstitute(
        int $id,
        string|int $recetaVersionId,
        string|int $suscripcionId,
        string|int $pacienteId,
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
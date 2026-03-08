<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Shared\Aggregate\AggregateRoot;

/**
 * @class Etiqueta
 */
class Etiqueta
{
    use AggregateRoot;

    /**
     * @var string|int|null
     */
    private $id;

    /**
     * @var string|int
     */
    private $recetaId;

    /**
     * @var string|int
     */
    private $suscripcionId;

    /**
     * @var string|int
     */
    private $pacienteId;

    /**
     * @var array
     */
    private $qrPayload;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $id,
        string|int $recetaId,
        string|int $suscripcionId,
        string|int $pacienteId,
        array $qrPayload = []
    ) {
        $this->id = $id;
        $this->recetaId = $recetaId;
        $this->suscripcionId = $suscripcionId;
        $this->pacienteId = $pacienteId;
        $this->qrPayload = $qrPayload;
    }

    public static function crear(
        string|int|null $id,
        string|int $recetaId,
        string|int $suscripcionId,
        string|int $pacienteId,
        array $qrPayload = []
    ): self {
        $self = new self(
            $id,
            $recetaId,
            $suscripcionId,
            $pacienteId,
            $qrPayload
        );

        // $self->record();

        return $self;
    }

    public static function reconstitute(
        int $id,
        string|int $recetaId,
        string|int $suscripcionId,
        string|int $pacienteId,
        array $qrPayload
    ): self {
        $self = new self(
            $id,
            $recetaId,
            $suscripcionId,
            $pacienteId,
            $qrPayload
        );

        return $self;
    }
}

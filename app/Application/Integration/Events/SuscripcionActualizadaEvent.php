<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class SuscripcionActualizadaEvent
 */
class SuscripcionActualizadaEvent
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var ?string
     */
    public $nombre;

    /**
     * @var string|null
     */
    public $pacienteId;

    /**
     * @var string|null
     */
    public $tipoServicio;

    /**
     * @var string|null
     */
    public $fechaInicio;

    /**
     * @var string|null
     */
    public $fechaFin;

    /**
     * Constructor
     */
    public function __construct(
        string $id,
        ?string $nombre,
        ?string $pacienteId = null,
        ?string $tipoServicio = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pacienteId = $pacienteId;
        $this->tipoServicio = $tipoServicio;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['id', 'suscripcionId', 'suscripcion_id', 'contratoId', 'contrato_id'], null, true),
            $p->getString(['nombre', 'name', 'tipoServicio', 'tipo_servicio']),
            $p->getString(['pacienteId', 'paciente_id']),
            $p->getString(['tipoServicio', 'tipo_servicio']),
            $p->getString(['fechaInicio', 'fecha_inicio']),
            $p->getString(['fechaFin', 'fecha_fin'])
        );
    }
}

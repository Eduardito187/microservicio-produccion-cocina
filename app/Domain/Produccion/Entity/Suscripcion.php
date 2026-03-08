<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Suscripcion
 */
class Suscripcion
{
    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var string
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
     * @var string|null
     */
    public $estado;

    /**
     * @var string|null
     */
    public $motivoCancelacion;

    /**
     * @var string|null
     */
    public $canceladoAt;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $id,
        string $nombre,
        ?string $pacienteId = null,
        ?string $tipoServicio = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        ?string $estado = 'ACTIVA',
        ?string $motivoCancelacion = null,
        ?string $canceladoAt = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pacienteId = $pacienteId;
        $this->tipoServicio = $tipoServicio;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->estado = $estado;
        $this->motivoCancelacion = $motivoCancelacion;
        $this->canceladoAt = $canceladoAt;
    }
}

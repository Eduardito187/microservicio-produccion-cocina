<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

/**
 * @class Suscripcion
 * @package App\Domain\Produccion\Entity
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
     *
     * @param string|int|null $id
     * @param string $nombre
     * @param string|null $pacienteId
     * @param string|null $tipoServicio
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @param string|null $estado
     * @param string|null $motivoCancelacion
     * @param string|null $canceladoAt
     */
    public function __construct(
        string|int|null $id,
        string $nombre,
        string|null $pacienteId = null,
        string|null $tipoServicio = null,
        string|null $fechaInicio = null,
        string|null $fechaFin = null,
        string|null $estado = 'ACTIVA',
        string|null $motivoCancelacion = null,
        string|null $canceladoAt = null
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

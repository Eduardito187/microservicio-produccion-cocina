<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class SuscripcionCreadaEvent
 * @package App\Application\Integration\Events
 */
class SuscripcionCreadaEvent
{
    /**
     * @var string
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
     * Constructor
     *
     * @param string $id
     * @param string $nombre
     * @param string|null $pacienteId
     * @param string|null $tipoServicio
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     */
    public function __construct(
        string $id,
        string $nombre,
        string|null $pacienteId = null,
        string|null $tipoServicio = null,
        string|null $fechaInicio = null,
        string|null $fechaFin = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->pacienteId = $pacienteId;
        $this->tipoServicio = $tipoServicio;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        $id = $p->getString(['id', 'suscripcionId', 'suscripcion_id', 'contratoId', 'contrato_id'], null, true);
        $tipoServicio = $p->getString(['tipoServicio', 'tipo_servicio']);
        $nombre = $p->getString(['nombre', 'name']);
        if (!is_string($nombre) || trim($nombre) === '') {
            if (is_string($tipoServicio) && trim($tipoServicio) !== '') {
                $nombre = trim($tipoServicio) . ' #' . $id;
            } else {
                $nombre = 'Contrato #' . $id;
            }
        }

        return new self(
            $id,
            $nombre,
            $p->getString(['pacienteId', 'paciente_id']),
            $tipoServicio,
            $p->getString(['fechaInicio', 'fecha_inicio']),
            $p->getString(['fechaFin', 'fecha_fin'])
        );
    }
}

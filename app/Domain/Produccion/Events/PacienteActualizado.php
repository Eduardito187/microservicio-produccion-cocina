<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class PacienteActualizado
 * @package App\Domain\Produccion\Events
 */
class PacienteActualizado extends BaseDomainEvent
{
    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string|null
     */
    private $documento;

    /**
     * @var string|int|null
     */
    private $suscripcionId;

    /**
     * Constructor
     *
     * @param string|int|null $pacienteId
     * @param string $nombre
     * @param string|null $documento
     * @param string|int|null $suscripcionId
     */
    public function __construct(
        string|int|null $pacienteId,
        string $nombre,
        string|null $documento,
        string|int|null $suscripcionId
    ) {
        parent::__construct($pacienteId);
        $this->nombre = $nombre;
        $this->documento = $documento;
        $this->suscripcionId = $suscripcionId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'documento' => $this->documento,
            'suscripcionId' => $this->suscripcionId,
        ];
    }
}

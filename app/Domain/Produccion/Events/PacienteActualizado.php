<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class PacienteActualizado
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
     */
    public function __construct(
        string|int|null $pacienteId,
        string $nombre,
        ?string $documento,
        string|int|null $suscripcionId
    ) {
        parent::__construct($pacienteId);
        $this->nombre = $nombre;
        $this->documento = $documento;
        $this->suscripcionId = $suscripcionId;
    }

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'documento' => $this->documento,
            'suscripcionId' => $this->suscripcionId,
        ];
    }
}

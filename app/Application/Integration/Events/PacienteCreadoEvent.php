<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class PacienteCreadoEvent
 * @package App\Application\Integration\Events
 */
class PacienteCreadoEvent
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
     * @var ?string
     */
    public $documento;

    /**
     * @var ?string
     */
    public $suscripcionId;

    /**
     * Constructor
     *
     * @param string $id
     * @param string $nombre
     * @param ?string $documento
     * @param ?string $suscripcionId
     */
    public function __construct(
        string $id,
        string $nombre,
        ?string $documento,
        ?string $suscripcionId
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->documento = $documento;
        $this->suscripcionId = $suscripcionId;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['id', 'pacienteId', 'paciente_id'], null, true),
            $p->getString(['nombre', 'name'], null, true),
            $p->getString(['documento', 'document']),
            $p->getString(['suscripcionId', 'suscripcion_id'])
        );
    }
}

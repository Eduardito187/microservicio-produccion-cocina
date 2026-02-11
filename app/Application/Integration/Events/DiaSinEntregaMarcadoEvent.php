<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class DiaSinEntregaMarcadoEvent
 * @package App\Application\Integration\Events
 */
class DiaSinEntregaMarcadoEvent
{
    /**
     * @var string
     */
    public $calendarioId;

    /**
     * @var ?string
     */
    public $fecha;

    /**
     * @var ?string
     */
    public $sucursalId;

    /**
     * Constructor
     *
     * @param string $calendarioId
     * @param ?string $fecha
     * @param ?string $sucursalId
     */
    public function __construct(
        string $calendarioId,
        ?string $fecha,
        ?string $sucursalId
    ) {
        $this->calendarioId = $calendarioId;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['calendarioId', 'calendario_id'], null, true),
            $p->getString(['fecha', 'date']),
            $p->getString(['sucursalId', 'sucursal_id'])
        );
    }
}

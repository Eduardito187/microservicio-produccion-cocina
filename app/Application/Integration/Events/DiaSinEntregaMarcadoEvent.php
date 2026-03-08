<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class DiaSinEntregaMarcadoEvent
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
     * Constructor
     */
    public function __construct(
        string $calendarioId,
        ?string $fecha
    ) {
        $this->calendarioId = $calendarioId;
        $this->fecha = $fecha;
    }

    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['calendarioId', 'calendario_id'], null, true),
            $p->getString(['fecha', 'date'])
        );
    }
}

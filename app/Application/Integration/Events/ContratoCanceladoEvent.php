<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class ContratoCanceladoEvent
 */
class ContratoCanceladoEvent
{
    /**
     * @var string
     */
    public $contratoId;

    /**
     * @var string|null
     */
    public $motivoCancelacion;

    /**
     * Constructor
     */
    public function __construct(string $contratoId, ?string $motivoCancelacion = null)
    {
        $this->contratoId = $contratoId;
        $this->motivoCancelacion = $motivoCancelacion;
    }

    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['contratoId', 'contrato_id', 'id', 'suscripcionId', 'suscripcion_id'], null, true),
            $p->getString(['motivoCancelacion', 'motivo_cancelacion'])
        );
    }
}

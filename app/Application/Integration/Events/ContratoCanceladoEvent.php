<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class ContratoCanceladoEvent
 * @package App\Application\Integration\Events
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
     *
     * @param string $contratoId
     * @param string|null $motivoCancelacion
     */
    public function __construct(string $contratoId, string|null $motivoCancelacion = null)
    {
        $this->contratoId = $contratoId;
        $this->motivoCancelacion = $motivoCancelacion;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['contratoId', 'contrato_id', 'id', 'suscripcionId', 'suscripcion_id'], null, true),
            $p->getString(['motivoCancelacion', 'motivo_cancelacion'])
        );
    }
}

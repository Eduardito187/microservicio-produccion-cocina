<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class EntregaFallidaEvent
 * @package App\Application\Integration\Events
 */
class EntregaFallidaEvent
{
        /**
     * @var string
     */
    public $paqueteId;

    /**
     * @var ?string
     */
    public $motivo;

    /**
     * @var ?string
     */
    public $occurredOn;

    /**
     * Constructor
     *
     * @param string $paqueteId
     * @param ?string $motivo
     * @param ?string $occurredOn
     */
    public function __construct(
        string $paqueteId,
        ?string $motivo,
        ?string $occurredOn
    ) {
        $this->paqueteId = $paqueteId;
        $this->motivo = $motivo;
        $this->occurredOn = $occurredOn;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['paqueteId', 'paquete_id'], null, true),
            $p->getString(['motivo', 'reason']),
            $p->getString(['occurredOn', 'occurred_on', 'timestamp'])
        );
    }
}

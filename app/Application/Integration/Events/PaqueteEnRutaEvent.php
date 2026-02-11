<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class PaqueteEnRutaEvent
 * @package App\Application\Integration\Events
 */
class PaqueteEnRutaEvent
{
        /**
     * @var string
     */
    public $paqueteId;

    /**
     * @var ?string
     */
    public $rutaId;

    /**
     * @var ?string
     */
    public $occurredOn;

    /**
     * Constructor
     *
     * @param string $paqueteId
     * @param ?string $rutaId
     * @param ?string $occurredOn
     */
    public function __construct(
        string $paqueteId,
        ?string $rutaId,
        ?string $occurredOn
    ) {
        $this->paqueteId = $paqueteId;
        $this->rutaId = $rutaId;
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
            $p->getString(['rutaId', 'ruta_id']),
            $p->getString(['occurredOn', 'occurred_on', 'timestamp'])
        );
    }
}

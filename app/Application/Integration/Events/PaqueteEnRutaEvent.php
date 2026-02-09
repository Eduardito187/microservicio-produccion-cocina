<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class PaqueteEnRutaEvent
{
    public function __construct(
        public readonly string $paqueteId,
        public readonly ?string $rutaId,
        public readonly ?string $occurredOn
    ) {
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

<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class EntregaFallidaEvent
{
    public function __construct(
        public readonly string $paqueteId,
        public readonly ?string $motivo,
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
            $p->getString(['motivo', 'reason']),
            $p->getString(['occurredOn', 'occurred_on', 'timestamp'])
        );
    }
}

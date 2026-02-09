<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class SuscripcionCreadaEvent
{
    public function __construct(
        public readonly string $id,
        public readonly string $nombre
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
            $p->getString(['id', 'suscripcionId', 'suscripcion_id'], null, true),
            $p->getString(['nombre', 'name'], null, true)
        );
    }
}

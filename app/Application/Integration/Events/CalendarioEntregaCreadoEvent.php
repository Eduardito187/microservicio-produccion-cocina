<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class CalendarioEntregaCreadoEvent
{
    public function __construct(
        public readonly string $id,
        public readonly string $fecha,
        public readonly string $sucursalId
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
            $p->getString(['id', 'calendarioId', 'calendario_id'], null, true),
            $p->getString(['fecha', 'date'], null, true),
            $p->getString(['sucursalId', 'sucursal_id'], null, true)
        );
    }
}

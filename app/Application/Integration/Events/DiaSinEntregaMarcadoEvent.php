<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class DiaSinEntregaMarcadoEvent
{
    public function __construct(
        public readonly string $calendarioId,
        public readonly ?string $fecha,
        public readonly ?string $sucursalId
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
            $p->getString(['calendarioId', 'calendario_id'], null, true),
            $p->getString(['fecha', 'date']),
            $p->getString(['sucursalId', 'sucursal_id'])
        );
    }
}

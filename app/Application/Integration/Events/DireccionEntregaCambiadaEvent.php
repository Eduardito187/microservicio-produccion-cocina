<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class DireccionEntregaCambiadaEvent
{
    public function __construct(
        public readonly ?string $paqueteId,
        public readonly ?string $itemDespachoId,
        public readonly ?string $direccionId
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
            $p->getString(['paqueteId', 'paquete_id']),
            $p->getString(['itemDespachoId', 'item_despacho_id']),
            $p->getString(['direccionId', 'direccion_id'], null, true)
        );
    }
}

<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class EntregaProgramadaEvent
{
    public function __construct(
        public readonly string $calendarioId,
        public readonly string $itemDespachoId,
        public readonly ?string $ordenProduccionId,
        public readonly ?array $items,
        public readonly ?array $itemsDespacho,
        public readonly ?string $pacienteId,
        public readonly ?string $direccionId,
        public readonly ?string $ventanaEntregaId
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
            $p->getString(['itemDespachoId', 'item_despacho_id'], null, true),
            $p->getString(['ordenProduccionId', 'orden_produccion_id', 'op_id']),
            $p->getArray(['items']),
            $p->getArray(['itemsDespacho', 'items_despacho']),
            $p->getString(['pacienteId', 'paciente_id']),
            $p->getString(['direccionId', 'direccion_id']),
            $p->getString(['ventanaEntregaId', 'ventana_entrega_id'])
        );
    }
}

<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class PacienteActualizadoEvent
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $nombre,
        public readonly ?string $documento,
        public readonly ?string $suscripcionId
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
            $p->getString(['id', 'pacienteId', 'paciente_id'], null, true),
            $p->getString(['nombre', 'name']),
            $p->getString(['documento', 'document']),
            $p->getString(['suscripcionId', 'suscripcion_id'])
        );
    }
}

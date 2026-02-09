<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class EntregaConfirmadaEvent
{
    public function __construct(
        public readonly string $paqueteId,
        public readonly ?string $fotoUrl,
        public readonly ?array $geo,
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
            $p->getString(['fotoUrl', 'foto_url', 'evidenciaUrl', 'evidencia_url']),
            $p->getArray(['geo', 'geolocalizacion', 'geolocation']),
            $p->getString(['occurredOn', 'occurred_on', 'timestamp'])
        );
    }
}

<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class DireccionCreadaEvent
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $nombre,
        public readonly string $linea1,
        public readonly ?string $linea2,
        public readonly ?string $ciudad,
        public readonly ?string $provincia,
        public readonly ?string $pais,
        public readonly ?array $geo
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
            $p->getString(['id', 'direccionId', 'direccion_id'], null, true),
            $p->getString(['nombre', 'name']),
            $p->getString(['linea1', 'linea_1', 'line1'], null, true),
            $p->getString(['linea2', 'linea_2', 'line2']),
            $p->getString(['ciudad', 'city']),
            $p->getString(['provincia', 'state', 'region']),
            $p->getString(['pais', 'country']),
            $p->getArray(['geo', 'geolocalizacion', 'geolocation'])
        );
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class EntregaConfirmadaEvent
 */
class EntregaConfirmadaEvent
{
    /**
     * @var string
     */
    public $paqueteId;

    /**
     * @var ?string
     */
    public $fotoUrl;

    /**
     * @var ?array
     */
    public $geo;

    /**
     * @var ?string
     */
    public $occurredOn;

    /**
     * Constructor
     */
    public function __construct(
        string $paqueteId,
        ?string $fotoUrl,
        ?array $geo,
        ?string $occurredOn
    ) {
        $this->paqueteId = $paqueteId;
        $this->fotoUrl = $fotoUrl;
        $this->geo = $geo;
        $this->occurredOn = $occurredOn;
    }

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

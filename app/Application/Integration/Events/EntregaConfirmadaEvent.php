<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class EntregaConfirmadaEvent
 * @package App\Application\Integration\Events
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
     *
     * @param string $paqueteId
     * @param ?string $fotoUrl
     * @param ?array $geo
     * @param ?string $occurredOn
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

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class DireccionGeocodificadaEvent
 * @package App\Application\Integration\Events
 */
class DireccionGeocodificadaEvent
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var ?array
     */
    public $geo;

    /**
     * Constructor
     *
     * @param string $id
     * @param ?array $geo
     */
    public function __construct(
        string $id,
        ?array $geo
    ) {
        $this->id = $id;
        $this->geo = $geo;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        $geo = $p->getArray(['geo', 'geolocalizacion', 'geolocation']);
        if ($geo === null) {
            $lat = $p->getString(['lat', 'latitude']);
            $lng = $p->getString(['lng', 'lon', 'longitude']);
            if ($lat !== null || $lng !== null) {
                $geo = [
                    'lat' => $lat,
                    'lng' => $lng,
                ];
            }
        }

        return new self(
            $p->getString(['id', 'direccionId', 'direccion_id'], null, true),
            $geo
        );
    }
}

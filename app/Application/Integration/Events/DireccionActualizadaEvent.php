<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class DireccionActualizadaEvent
 * @package App\Application\Integration\Events
 */
class DireccionActualizadaEvent
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var ?string
     */
    public $nombre;

    /**
     * @var ?string
     */
    public $linea1;

    /**
     * @var ?string
     */
    public $linea2;

    /**
     * @var ?string
     */
    public $ciudad;

    /**
     * @var ?string
     */
    public $provincia;

    /**
     * @var ?string
     */
    public $pais;

    /**
     * @var ?array
     */
    public $geo;

    /**
     * Constructor
     *
     * @param string $id
     * @param ?string $nombre
     * @param ?string $linea1
     * @param ?string $linea2
     * @param ?string $ciudad
     * @param ?string $provincia
     * @param ?string $pais
     * @param ?array $geo
     */
    public function __construct(
        string $id,
        ?string $nombre,
        ?string $linea1,
        ?string $linea2,
        ?string $ciudad,
        ?string $provincia,
        ?string $pais,
        ?array $geo
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->linea1 = $linea1;
        $this->linea2 = $linea2;
        $this->ciudad = $ciudad;
        $this->provincia = $provincia;
        $this->pais = $pais;
        $this->geo = $geo;
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
            $p->getString(['linea1', 'linea_1', 'line1']),
            $p->getString(['linea2', 'linea_2', 'line2']),
            $p->getString(['ciudad', 'city']),
            $p->getString(['provincia', 'state', 'region']),
            $p->getString(['pais', 'country']),
            $p->getArray(['geo', 'geolocalizacion', 'geolocation'])
        );
    }
}

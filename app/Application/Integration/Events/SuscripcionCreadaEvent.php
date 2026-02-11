<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class SuscripcionCreadaEvent
 * @package App\Application\Integration\Events
 */
class SuscripcionCreadaEvent
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $nombre;

    /**
     * Constructor
     *
     * @param string $id
     * @param string $nombre
     */
    public function __construct(
        string $id,
        string $nombre
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['id', 'suscripcionId', 'suscripcion_id'], null, true),
            $p->getString(['nombre', 'name'], null, true)
        );
    }
}

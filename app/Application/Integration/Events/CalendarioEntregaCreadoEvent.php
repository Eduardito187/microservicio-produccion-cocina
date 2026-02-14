<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class CalendarioEntregaCreadoEvent
 * @package App\Application\Integration\Events
 */
class CalendarioEntregaCreadoEvent
{
        /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $fecha;

    /**
     * Constructor
     *
     * @param string $id
     * @param string $fecha
     */
    public function __construct(
        string $id,
        string $fecha
    ) {
        $this->id = $id;
        $this->fecha = $fecha;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['id', 'calendarioId', 'calendario_id'], null, true),
            $p->getString(['fecha', 'date'], null, true)
        );
    }
}

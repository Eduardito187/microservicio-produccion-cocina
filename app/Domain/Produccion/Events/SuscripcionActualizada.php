<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

class SuscripcionActualizada extends BaseDomainEvent
{
    /**
     * Constructor
     *
     * @param string|int|null $suscripcionId
     * @param string $nombre
     */
        /**
     * @var string
     */
    private $nombre;

/**
 * Constructor
 *
 * @param string|int|null $suscripcionId
 * @param string $nombre
 */
public function __construct(
        string|int|null $suscripcionId,
        string $nombre
    ) {
        $this->nombre = $nombre;

        parent::__construct($suscripcionId);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
        ];
    }
}

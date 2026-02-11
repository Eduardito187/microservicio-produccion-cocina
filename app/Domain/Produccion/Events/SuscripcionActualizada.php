<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class SuscripcionActualizada
 * @package App\Domain\Produccion\Events
 */
class SuscripcionActualizada extends BaseDomainEvent
{
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
        parent::__construct($suscripcionId);
        $this->nombre = $nombre;
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

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class SuscripcionCreada
 * @package App\Domain\Produccion\Events
 */
class SuscripcionCreada extends BaseDomainEvent
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

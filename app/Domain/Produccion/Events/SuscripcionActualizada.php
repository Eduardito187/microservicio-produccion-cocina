<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class SuscripcionActualizada
 */
class SuscripcionActualizada extends BaseDomainEvent
{
    /**
     * @var string
     */
    private $nombre;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $suscripcionId,
        string $nombre
    ) {
        parent::__construct($suscripcionId);
        $this->nombre = $nombre;
    }

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
        ];
    }
}

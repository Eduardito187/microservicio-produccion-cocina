<?php

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
    public function __construct(
        string|int|null $suscripcionId,
        private readonly string $nombre
    ) {
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
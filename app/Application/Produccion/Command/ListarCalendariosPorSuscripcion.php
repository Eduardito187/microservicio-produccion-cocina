<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ListarCalendariosPorSuscripcion
 */
class ListarCalendariosPorSuscripcion
{
    /**
     * @var string
     */
    public $suscripcionId;

    public function __construct(string $suscripcionId)
    {
        $this->suscripcionId = $suscripcionId;
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ListarOrdenesPorSuscripcion
 */
class ListarOrdenesPorSuscripcion
{
    /**
     * @var string
     */
    public $suscripcionId;

    /**
     * Constructor
     */
    public function __construct(string $suscripcionId)
    {
        $this->suscripcionId = $suscripcionId;
    }
}

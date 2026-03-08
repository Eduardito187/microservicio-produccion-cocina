<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

/**
 * @class CrearCalendario
 */
class CrearCalendario
{
    /**
     * @var DateTimeImmutable
     */
    public $fecha;

    /**
     * Constructor
     */
    public function __construct(DateTimeImmutable $fecha)
    {
        $this->fecha = $fecha;
    }
}

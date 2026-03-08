<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

/**
 * @class ActualizarCalendario
 */
class ActualizarCalendario
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var DateTimeImmutable
     */
    public $fecha;

    /**
     * Constructor
     */
    public function __construct(string $id, DateTimeImmutable $fecha)
    {
        $this->id = $id;
        $this->fecha = $fecha;
    }
}

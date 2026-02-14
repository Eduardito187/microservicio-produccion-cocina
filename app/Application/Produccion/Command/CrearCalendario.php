<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

/**
 * @class CrearCalendario
 * @package App\Application\Produccion\Command
 */
class CrearCalendario
{
    /**
     * @var DateTimeImmutable
     */
    public $fecha;

    /**
     * Constructor
     *
     * @param DateTimeImmutable $fecha
     */
    public function __construct(DateTimeImmutable $fecha)
    {
        $this->fecha = $fecha;
    }
}

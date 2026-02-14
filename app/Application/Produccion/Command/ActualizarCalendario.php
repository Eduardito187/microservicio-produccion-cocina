<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

/**
 * @class ActualizarCalendario
 * @package App\Application\Produccion\Command
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
     *
     * @param string $id
     * @param DateTimeImmutable $fecha
     */
    public function __construct(string $id, DateTimeImmutable $fecha)
    {
        $this->id = $id;
        $this->fecha = $fecha;
    }
}

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
     * @var string
     */
    public $sucursalId;

    /**
     * Constructor
     *
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     */
    public function __construct(DateTimeImmutable $fecha, string $sucursalId)
    {
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
    }
}

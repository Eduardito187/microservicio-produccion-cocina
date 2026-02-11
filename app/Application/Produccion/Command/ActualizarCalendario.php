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
     * @var string
     */
    public $sucursalId;

    /**
     * Constructor
     *
     * @param string $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     */
    public function __construct(string $id, DateTimeImmutable $fecha, string $sucursalId)
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
    }
}

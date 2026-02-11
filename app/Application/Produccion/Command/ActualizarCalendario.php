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
     * @var int
     */
    public string $id;

    /**
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $fecha;

    /**
     * @var string
     */
    public string $sucursalId;

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

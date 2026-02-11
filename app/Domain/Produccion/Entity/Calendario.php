<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

use DateTimeImmutable;

/**
 * @class Calendario
 * @package App\Domain\Produccion\Entity
 */
class Calendario
{
    /**
     * @var string|int|null
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
     * @param string|int|null $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     */
    public function __construct(string|int|null $id, DateTimeImmutable $fecha, string $sucursalId)
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
    }
}

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
     * Constructor
     *
     * @param string|int|null $id
     * @param DateTimeImmutable $fecha
     */
    public function __construct(string|int|null $id, DateTimeImmutable $fecha)
    {
        $this->id = $id;
        $this->fecha = $fecha;
    }
}

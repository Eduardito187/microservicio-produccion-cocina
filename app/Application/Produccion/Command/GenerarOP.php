<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

/**
 * @class GenerarOP
 */
class GenerarOP
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var DateTimeImmutable
     */
    public $fecha;

    /**
     * @var array
     */
    public $items;

    /**
     * Constructor
     */
    public function __construct(?string $id, DateTimeImmutable $fecha, array $items)
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->items = $items;
    }
}

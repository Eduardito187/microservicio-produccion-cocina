<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

/**
 * @class GenerarOP
 * @package App\Application\Produccion\Command
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
     *
     * @param ?string $id
     * @param DateTimeImmutable $fecha
     * @param array $items
     */
    public function __construct(?string $id, DateTimeImmutable $fecha, array $items)
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->items = $items;
    }
}

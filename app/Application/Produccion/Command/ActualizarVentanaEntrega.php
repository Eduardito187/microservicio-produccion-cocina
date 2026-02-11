<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

/**
 * @class ActualizarVentanaEntrega
 * @package App\Application\Produccion\Command
 */
class ActualizarVentanaEntrega
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var DateTimeImmutable
     */
    public $desde;

    /**
     * @var DateTimeImmutable
     */
    public $hasta;

    /**
     * Constructor
     *
     * @param string $id
     * @param DateTimeImmutable $desde
     * @param DateTimeImmutable $hasta
     */
    public function __construct(string $id, DateTimeImmutable $desde, DateTimeImmutable $hasta)
    {
        $this->id = $id;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }
}

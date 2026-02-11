<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

/**
 * @class CrearVentanaEntrega
 * @package App\Application\Produccion\Command
 */
class CrearVentanaEntrega
{
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
     * @param DateTimeImmutable $desde
     * @param DateTimeImmutable $hasta
     */
    public function __construct(DateTimeImmutable $desde, DateTimeImmutable $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

/**
 * @class CrearVentanaEntrega
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
     */
    public function __construct(DateTimeImmutable $desde, DateTimeImmutable $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
    }
}

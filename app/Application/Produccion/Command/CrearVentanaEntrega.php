<?php

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

class CrearVentanaEntrega
{
    /**
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $desde;

    /**
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $hasta;

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

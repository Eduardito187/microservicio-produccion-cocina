<?php

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

class ActualizarVentanaEntrega
{
    /**
     * @var int
     */
    public int $id;

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
     * @param int $id
     * @param DateTimeImmutable $desde
     * @param DateTimeImmutable $hasta
     */
    public function __construct(int $id, DateTimeImmutable $desde, DateTimeImmutable $hasta)
    {
        $this->id = $id;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }
}

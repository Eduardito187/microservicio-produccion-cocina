<?php

namespace App\Domain\Produccion\Entity;

use DateTimeImmutable;

class VentanaEntrega
{
    /**
     * @var int|null
     */
    public int|null $id;

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
     * @param int|null $id
     * @param DateTimeImmutable $desde
     * @param DateTimeImmutable $hasta
     */
    public function __construct(
        int|null $id,
        DateTimeImmutable $desde,
        DateTimeImmutable $hasta
    ) {
        $this->id = $id;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }
}

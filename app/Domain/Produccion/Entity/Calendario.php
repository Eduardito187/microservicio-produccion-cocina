<?php

namespace App\Domain\Produccion\Entity;

use DateTimeImmutable;

class Calendario
{
    /**
     * @var int|null
     */
    public int|null $id;

    /**
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $fecha;

    /**
     * @var string
     */
    public string $sucursalId;

    /**
     * Constructor
     *
     * @param int|null $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     */
    public function __construct(int|null $id, DateTimeImmutable $fecha, string $sucursalId)
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
    }
}

<?php

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

class ActualizarCalendario
{
    /**
     * @var int
     */
    public int $id;

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
     * @param int $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     */
    public function __construct(int $id, DateTimeImmutable $fecha, string $sucursalId)
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
    }
}

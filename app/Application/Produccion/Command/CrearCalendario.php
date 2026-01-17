<?php

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

class CrearCalendario
{
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
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     */
    public function __construct(DateTimeImmutable $fecha, string $sucursalId)
    {
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
    }
}




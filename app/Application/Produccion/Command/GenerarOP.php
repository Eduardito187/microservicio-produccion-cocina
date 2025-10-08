<?php

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

class GenerarOP
{
    /**
     * @var DateTimeImmutable
     */
    public readonly DateTimeImmutable $fecha;

    /**
     * @var string
     */
    public readonly string $sucursalId;

    /**
     * @var array
     */
    public readonly array $items;

    /**
     * Constructor
     * 
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     * @param array $items
     */
    public function __construct(
        DateTimeImmutable $fecha,
        string $sucursalId,
        array $items
    ) {
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
        $this->items = $items;
    }
}
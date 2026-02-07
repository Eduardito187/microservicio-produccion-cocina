<?php

namespace App\Application\Produccion\Command;

use DateTimeImmutable;

class GenerarOP
{
    /** 
     * @var int|null
     */
    public ?string $id;

    /** 
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $fecha;

    /** 
     * @var int|string
     */
    public int|string $sucursalId;

    /** 
     * @var array
     */
    public array $items;

    /**
     * Constructor
     * 
     * @param mixed $id
     * @param DateTimeImmutable $fecha
     * @param int|string $sucursalId
     * @param array $items
     */
    public function __construct(?string $id, DateTimeImmutable $fecha, int|string $sucursalId, array $items)
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
        $this->items = $items;
    }
}




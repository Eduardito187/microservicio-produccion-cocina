<?php

namespace App\Domain\Produccion\ValueObject;

use App\Domain\Shared\ValueObject;
use DateTimeImmutable;

class OrdenProduccion extends ValueObject
{
    /**
     * @var int|null
     */
    public readonly int|null $id;

    /**
     * @var DateTimeImmutable
     */
    public readonly DateTimeImmutable $fecha;

    /**
     * @var string
     */
    public readonly string $sucursalId;

    /**
     * @var array|OrderItems
     */
    public readonly array|OrderItems $items;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     * @param array|OrderItems $items
     */
    public function __construct(int|null $id, DateTimeImmutable $fecha, string $sucursalId, array|OrderItems $items)
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
        $this->items = $items;

        // Example: emit domain event (to be persisted via outbox in infra)
        // $this->record(new OrdenProduccionGenerada($this->id, ...));
    }

    /**
     * @param int|null $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     * @param array|OrderItems $items
     * @return OrdenProduccion
     */
    public static function generar(int|null $id, DateTimeImmutable $fecha, string $sucursalId, array|OrderItems $items): self
    {
        return new self($id, $fecha, $sucursalId, $items);
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return array|OrderItems
     */
    public function items(): array|OrderItems
    {
        return $this->items;
    }
}
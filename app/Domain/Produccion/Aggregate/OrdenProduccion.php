<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Produccion\Model\OrderItems;
use App\Domain\Shared\AggregateRoot;
use DateTimeImmutable;
use DomainException;

class OrdenProduccion
{
    use AggregateRoot;

    /**
     * @var int|null
     */
    private int|null $id;

    /**
     * @var string|DateTimeImmutable
     */
    private DateTimeImmutable $fecha;

    /**
     * @var string
     */
    private string $sucursalId;

    /**
     * @var EstadoOP
     */
    private EstadoOP $estado;

    /**
     * @var array|OrderItems
     */
    private array|OrderItems $items;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     * @param EstadoOP $estado
     * @param array|OrderItems $items
     * @throws DomainException
     */
    private function __construct(
        int|null $id,
        DateTimeImmutable $fecha,
        string $sucursalId,
        EstadoOP $estado,
        array|OrderItems $items
    ) {
        if ($items->count() === 0) {
            throw new DomainException('La OP debe tener al menos un ítem.');
        }

        $this->id = $id;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
        $this->estado = $estado;
        $this->items = $items;
    }

    /**
     * @param int|null $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     * @param OrderItems $items
     * @return OrdenProduccion
     */
    public static function crear(
        int|null $id,
        DateTimeImmutable $fecha,
        string $sucursalId,
        OrderItems $items
    ): self {
        $self = new self($id, $fecha, $sucursalId, EstadoOP::CREADA, $items);

        $self->record(new OrdenProduccionCreada(
            $id,
            $fecha,
            $sucursalId
        ));

        return $self;
    }

    /**
     * @param int $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     * @param EstadoOP $estado
     * @param OrderItems $items
     * @return OrdenProduccion
     */
    public static function reconstitute(
        int $id,
        DateTimeImmutable $fecha,
        string $sucursalId,
        EstadoOP $estado,
        OrderItems $items
    ): self {
        $self = new self($id, $fecha, $sucursalId, $estado, $items);

        return $self;
    }

    public function cerrar(): void
    {
        if (!in_array($this->estado, [EstadoOP::CREADA, EstadoOP::EN_PROCESO], true)) {
            throw new DomainException('La OP no se puede cerrar en su estado actual.');
        }

        $this->estado = EstadoOP::CERRADA;

        //$this->record(new OrdenProduccionCerrada($this->id, $this->fecha));
    }

    /**
     * @param OrderItems $nuevos
     * @throws DomainException
     * @return void
     */
    public function agregarItems(OrderItems $nuevos): void
    {
        if ($this->estado !== EstadoOP::CREADA) {
            throw new DomainException('Solo se pueden agregar ítems cuando la OP está CREADA.');
        }

        $this->items = $this->items->mergedWith($nuevos);
    }

    /**
     * @return int|null
     */
    public function id(): int|null
    {
        return $this->id;
    }

    /**
     * @return string|DateTimeImmutable
     */
    public function fecha(): string|DateTimeImmutable
    {
        return $this->fecha;
    }

    /**
     * @return string
     */
    public function sucursalId(): string
    {
        return $this->sucursalId;
    }

    /**
     * @return EstadoOP
     */
    public function estado(): EstadoOP
    {
        return $this->estado;
    }

    /**
     * @return OrderItems
     */
    public function items(): OrderItems
    {
        return $this->items;
    }
}
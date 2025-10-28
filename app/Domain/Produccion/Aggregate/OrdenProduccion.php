<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Domain\Produccion\Events\OrdenProduccionPlanificada;
use App\Domain\Produccion\Events\OrdenProduccionProcesada;
use App\Domain\Produccion\Events\OrdenProduccionCerrada;
use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Shared\Aggregate\AggregateRoot;
use App\Domain\Produccion\Model\OrderItems;
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
    private int|string $sucursalId;

    /**
     * @var EstadoOP
     */
    private EstadoOP $estado;

    /**
     * @var array|OrderItems
     */
    private array|OrderItems $items;

    /**
     * @var array
     */
    private array $batches;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param DateTimeImmutable $fecha
     * @param int|string $sucursalId
     * @param EstadoOP $estado
     * @param array|OrderItems $items
     * @param array $batches
     * @throws DomainException
     */
    private function __construct(
        int|null $id,
        DateTimeImmutable $fecha,
        int|string $sucursalId,
        EstadoOP $estado,
        array|OrderItems $items,
        array $batches
    ) {
        if ($items->count() === 0) {
            throw new DomainException('La OP debe tener al menos un ítem.');
        }

        $this->id = $id;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
        $this->estado = $estado;
        $this->items = $items;
        $this->batches = $batches;
    }

    /**
     * @param DateTimeImmutable $fecha
     * @param int|string $sucursalId
     * @param OrderItems $items
     * @param array $batches
     * @param int|null $id
     * @return OrdenProduccion
     */
    public static function crear(DateTimeImmutable $fecha, string $sucursalId, OrderItems $items, array $batches = [], int|null $id = null): self
    {
        $self = new self($id, $fecha, $sucursalId, EstadoOP::CREADA, $items, $batches);

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
     * @param int|string $sucursalId
     * @param EstadoOP $estado
     * @param OrderItems $items
     * @param array $batches
     * @return OrdenProduccion
     */
    public static function reconstitute(
        int $id,
        DateTimeImmutable $fecha,
        string $sucursalId,
        EstadoOP $estado,
        OrderItems $items,
        array $batches
    ): self {
        $self = new self($id, $fecha, $sucursalId, $estado, $items, $batches);

        return $self;
    }

    /**
     * @throws DomainException
     * @return void
     */
    public function planificar(): void
    {
        if (!in_array($this->estado, [EstadoOP::CREADA], true)) {
            throw new DomainException('No se puede planificar en su estado actual.');
        }

        $this->estado = EstadoOP::PLANIFICADA;
        $this->record(new OrdenProduccionPlanificada($this->id, $this->fecha));
    }

    /**
     * @throws DomainException
     * @return void
     */
    public function procesar(): void
    {
        if (!in_array($this->estado, [EstadoOP::PLANIFICADA], true)) {
            throw new DomainException('No se puede procesar en su estado actual.');
        }

        $this->estado = EstadoOP::EN_PROCESO;
        $this->record(new OrdenProduccionProcesada($this->id, $this->fecha));
    }

    /**
     * @throws DomainException
     * @return void
     */
    public function cerrar(): void
    {
        if (!in_array($this->estado, [EstadoOP::EN_PROCESO], true)) {
            throw new DomainException('No se puede cerrar en su estado actual.');
        }

        $this->estado = EstadoOP::CERRADA;
        $this->record(new OrdenProduccionCerrada($this->id, $this->fecha));
    }

    /**
     * @param OrderItems $items
     * @throws DomainException
     * @return void
     */
    public function agregarItems(OrderItems $items): void
    {
        if ($this->estado !== EstadoOP::CREADA) {
            throw new DomainException('Solo se pueden agregar ítems cuando la OP está CREADA.');
        }

        $this->items = $this->items->mergedWith($items);
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
     * @return int|string
     */
    public function sucursalId(): int|string
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

    /**
     * @return AggregateProduccionBatch[]
     */
    public function batches(): array
    {
        return $this->batches;
    }
}
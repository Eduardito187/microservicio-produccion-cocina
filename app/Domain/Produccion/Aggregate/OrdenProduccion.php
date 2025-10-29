<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Domain\Produccion\Events\OrdenProduccionPlanificada;
use App\Domain\Produccion\Events\OrdenProduccionProcesada;
use App\Domain\Produccion\Events\OrdenProduccionCerrada;
use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Shared\Aggregate\AggregateRoot;
use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\Entity\OrdenItem;
use App\Domain\Produccion\Enum\EstadoOP;
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
     * @var array
     */
    private array $items;

    /**
     * @var array
     */
    private array $batches;

    /**
     * @var array
     */
    private array $itemsDespacho;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param DateTimeImmutable $fecha
     * @param int|string $sucursalId
     * @param EstadoOP $estado
     * @param array $items
     * @param array $batches
     * @param array $itemsDespacho
     */
    private function __construct(
        int|null $id,
        DateTimeImmutable $fecha,
        int|string $sucursalId,
        EstadoOP $estado,
        array $items,
        array $batches,
        array $itemsDespacho
    ) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
        $this->estado = $estado;
        $this->items = $items;
        $this->batches = $batches;
        $this->itemsDespacho = $itemsDespacho;
    }

    /**
     * @param DateTimeImmutable $fecha
     * @param int|string $sucursalId
     * @param array $items
     * @param array $batches
     * @param array $itemsDespacho
     * @param int|null $id
     * @return OrdenProduccion
     */
    public static function crear(
        DateTimeImmutable $fecha,
        string $sucursalId,
        array $items =  [],
        array $batches = [],
        array $itemsDespacho = [],
        int|null $id = null
    ): self {
        $self = new self($id, $fecha, $sucursalId, EstadoOP::CREADA, $items, $batches, $itemsDespacho);

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
     * @param array $items
     * @param array $batches
     * @param array $itemsDespacho
     * @return OrdenProduccion
     */
    public static function reconstitute(
        int $id,
        DateTimeImmutable $fecha,
        string $sucursalId,
        EstadoOP $estado,
        array $items,
        array $batches,
        array $itemsDespacho
    ): self {
        $self = new self($id, $fecha, $sucursalId, $estado, $items, $batches, $itemsDespacho);

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
     * @param array $data
     * @throws DomainException
     * @return void
     */
    public function agregarItems(array $data): void
    {
        if ($this->estado !== EstadoOP::CREADA) {
            throw new DomainException('Solo se pueden agregar ítems cuando la OP está CREADA.');
        }

        $items = [];

        foreach ($data as $item) {
            $items[] = new OrdenItem(
                null,
                null,
                null,
                new Qty($item['qty']),
                new Sku($item['sku'])
            );
        }

        $this->items = $items;
    }

    /**
     * @return ItemDespacho[]
     */
    public function generarBatches(): void
    {
        $items = [];

        foreach ($this->items() as $key => $item) {
            $items[] = new AggregateProduccionBatch(
                null,
                $this->id,
                $item->productId,
                1,
                1,
                1,
                1,
                0,
                50,
                EstadoPlanificado::PROGRAMADO,
                0,
                $item->qty,
                $key + 1
            );
        }

        $this->batches = $items;
    }

    /**
     * @return void
     */
    public function generarItemsDespacho(): void
    {
        $items = [];

        foreach ($this->items() as $item) {
            $items[] = new ItemDespacho(null, $this->id, $item->productId, null);
        }

        $this->itemsDespacho = $items;
    }

    /**
     * @return void
     */
    public function despacharBatches(): void
    {
        foreach ($this->batches() as $item) {
            $item->despachar();
        }
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
     * @return OrdenItem[]
     */
    public function items(): array
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

    /**
     * @return ItemDespacho[]
     */
    public function itemsDespacho(): array
    {
        return $this->itemsDespacho;
    }
}
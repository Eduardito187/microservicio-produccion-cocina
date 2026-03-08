<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\Entity\OrdenItem;
use App\Domain\Produccion\Enum\EstadoOP;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\Events\OrdenProduccionCerrada;
use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Produccion\Events\OrdenProduccionDespachada;
use App\Domain\Produccion\Events\OrdenProduccionPlanificada;
use App\Domain\Produccion\Events\OrdenProduccionProcesada;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Shared\Aggregate\AggregateRoot;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\Uuid;

/**
 * @class OrdenProduccion
 */
class OrdenProduccion
{
    use AggregateRoot;

    /**
     * @var string|int|null
     */
    private $id;

    /**
     * @var DateTimeImmutable
     */
    private $fecha;

    /**
     * @var EstadoOP
     */
    private $estado;

    /**
     * @var array
     */
    private $items;

    /**
     * @var array
     */
    private $batches;

    /**
     * @var array
     */
    private $itemsDespacho;

    /**
     * Constructor
     */
    private function __construct(
        string|int|null $id,
        DateTimeImmutable $fecha,
        EstadoOP $estado,
        array $items,
        array $batches,
        array $itemsDespacho
    ) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->estado = $estado;
        $this->items = $items;
        $this->batches = $batches;
        $this->itemsDespacho = $itemsDespacho;
    }

    public static function crear(
        DateTimeImmutable $fecha,
        array $items = [],
        array $batches = [],
        array $itemsDespacho = [],
        string|int|null $id = null
    ): self {
        $resolvedId = $id ?? Uuid::uuid4()->toString();
        $self = new self($resolvedId, $fecha, EstadoOP::CREADA, $items, $batches, $itemsDespacho);

        $self->record(new OrdenProduccionCreada(
            $resolvedId,
            $fecha,
            'CREADA',
            count($self->items),
            count($self->batches),
            count($self->itemsDespacho)
        ));

        return $self;
    }

    /**
     * @param  int  $id
     */
    public static function reconstitute(
        string|int|null $id,
        DateTimeImmutable $fecha,
        EstadoOP $estado,
        array $items,
        array $batches,
        array $itemsDespacho
    ): self {
        $self = new self($id, $fecha, $estado, $items, $batches, $itemsDespacho);

        return $self;
    }

    /**
     * @throws DomainException
     */
    public function planificar(): void
    {
        if (! in_array($this->estado, [EstadoOP::CREADA], true)) {
            throw new DomainException('No se puede planificar en su estado actual.');
        }

        $this->estado = EstadoOP::PLANIFICADA;
        $this->record(new OrdenProduccionPlanificada(
            $this->id,
            $this->fecha,
            'CREADA',
            'PLANIFICADA',
            count($this->items),
            count($this->batches),
            count($this->itemsDespacho)
        ));
    }

    /**
     * @throws DomainException
     */
    public function procesar(): void
    {
        if (! in_array($this->estado, [EstadoOP::PLANIFICADA], true)) {
            throw new DomainException('No se puede procesar en su estado actual.');
        }

        $this->estado = EstadoOP::EN_PROCESO;
        $this->record(new OrdenProduccionProcesada($this->id, $this->fecha));
    }

    /**
     * @throws DomainException
     */
    public function cerrar(): void
    {
        if (! in_array($this->estado, [EstadoOP::EN_PROCESO], true)) {
            throw new DomainException('No se puede cerrar en su estado actual.');
        }

        $this->estado = EstadoOP::CERRADA;
        $this->record(new OrdenProduccionCerrada($this->id, $this->fecha));
    }

    /**
     * @throws DomainException
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

    public function generarBatches(string|int $porcionId): void
    {
        $items = [];

        foreach ($this->items() as $key => $item) {
            $items[] = AggregateProduccionBatch::crear(
                null,
                $this->id,
                $item->productId,
                $porcionId,
                $item->qty()->value,
                0,
                50,
                EstadoPlanificado::PROGRAMADO,
                0,
                $item->qty,
                $key + 1,
                []
            );
        }

        $this->batches = $items;
    }

    public function generarItemsDespacho(
        array $itemsDespacho,
        string|int|null $pacienteId,
        string|int|null $direccionId,
        string|int|null $ventanaEntregaId
    ): void {
        $items = [];

        foreach ($this->items() as $item) {
            $items[] = new ItemDespacho(
                null,
                $this->id,
                $item->productId,
                null,
                $pacienteId,
                $direccionId,
                $ventanaEntregaId
            );
        }

        $this->itemsDespacho = $items;
        $this->record(new OrdenProduccionDespachada($this->id, $this->fecha, count($items)));
    }

    public function despacharBatches(): void
    {
        foreach ($this->batches() as $item) {
            $item->despachar();
        }
    }

    public function procesarBatches(): void
    {
        foreach ($this->batches() as $item) {
            $item->procesar();
        }
    }

    public function id(): string|int|null
    {
        return $this->id;
    }

    public function fecha(): string|DateTimeImmutable
    {
        return $this->fecha;
    }

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

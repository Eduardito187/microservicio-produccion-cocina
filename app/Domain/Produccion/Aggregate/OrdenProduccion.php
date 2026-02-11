<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Domain\Produccion\Events\OrdenProduccionPlanificada;
use App\Domain\Produccion\Events\OrdenProduccionProcesada;
use App\Domain\Produccion\Events\OrdenProduccionCerrada;
use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Produccion\Events\OrdenProduccionDespachada;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Shared\Aggregate\AggregateRoot;
use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\Entity\OrdenItem;
use App\Domain\Produccion\Enum\EstadoOP;
use DateTimeImmutable;
use DomainException;

/**
 * @class OrdenProduccion
 * @package App\Domain\Produccion\Aggregate
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
     * @var int|string
     */
    private $sucursalId;

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
     *
     * @param string|int|null $id
     * @param DateTimeImmutable $fecha
     * @param int|string $sucursalId
     * @param EstadoOP $estado
     * @param array $items
     * @param array $batches
     * @param array $itemsDespacho
     */
    private function __construct(
        string|int|null $id,
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
     * @param string|int|null $id
     * @return OrdenProduccion
     */
    public static function crear(
        DateTimeImmutable $fecha,
        string $sucursalId,
        array $items =  [],
        array $batches = [],
        array $itemsDespacho = [],
        string|int|null $id = null
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
        string|int|null $id,
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
     * @param string|int $estacionId
     * @param string|int $recetaVersionId
     * @param string|int $porcionId
     * @return void
     */
    public function generarBatches(string|int $estacionId, string|int $recetaVersionId, string|int $porcionId): void
    {
        $items = [];

        foreach ($this->items() as $key => $item) {
            $items[] = AggregateProduccionBatch::crear(
                null,
                $this->id,
                $item->productId,
                $estacionId,
                $recetaVersionId,
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

    /**
     * @param array $itemsDespacho
     * @param string|int|null $pacienteId
     * @param string|int|null $direccionId
     * @param string|int|null $ventanaEntregaId
     * @return void
     */
    public function generarItemsDespacho(
        array $itemsDespacho,
        string|int|null $pacienteId,
        string|int|null $direccionId,
        string|int|null $ventanaEntregaId
    ): void
    {
        $itemsDespachoBySku = [];

        foreach ($itemsDespacho as $item) {
            if (!isset($item['sku'], $item['recetaVersionId'])) {
                continue;
            }

            $recetaVersionId = $item['recetaVersionId'];
            if (is_string($recetaVersionId)) {
                $recetaVersionId = trim($recetaVersionId);
            }
            $itemsDespachoBySku[strtoupper(trim($item['sku']))] = $recetaVersionId;
        }

        $items = [];

        foreach ($this->items() as $item) {
            $sku = $item->sku()->value;
            $recetaVersionId = $itemsDespachoBySku[$sku] ?? null;

            $items[] = new ItemDespacho(
                null,
                $this->id,
                $item->productId,
                null,
                $recetaVersionId,
                $pacienteId,
                $direccionId,
                $ventanaEntregaId
            );
        }

        $this->itemsDespacho = $items;
        $this->record(new OrdenProduccionDespachada($this->id, $this->fecha, count($items)));
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
     * @return void
     */
    public function procesarBatches(): void
    {
        foreach ($this->batches() as $item) {
            $item->procesar();
        }
    }

    /**
     * @return string|int|null
     */
    public function id(): string|int|null
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

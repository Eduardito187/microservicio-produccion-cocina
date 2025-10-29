<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\ProduccionBatch as ProduccionBatchModel;
use App\Infrastructure\Persistence\Model\OrdenProduccion as OrdenProduccionModel;
use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Infrastructure\Persistence\Model\OrderItem as OrdenProduccionItemModel;
use App\Infrastructure\Persistence\Model\ItemDespacho as ItemDespachoModel;
use App\Domain\Produccion\Aggregate\ItemDespacho as AggregateItemDespacho;
use App\Infrastructure\Persistence\Repository\ProduccionBatchRepository;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Infrastructure\Persistence\Repository\ItemDespachoRepository;
use App\Domain\Produccion\Aggregate\OrdenItem as AggregateOrdenItem;
use App\Infrastructure\Persistence\Repository\OrdenItemRepository;
use App\Domain\Produccion\Model\OrderItems as ModelOrderItems;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Aggregate\EstadoPlanificado;
use App\Domain\Produccion\ValueObjects\ItemDespacho;
use App\Domain\Produccion\ValueObjects\OrderItem;
use App\Domain\Produccion\Aggregate\EstadoOP;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use DateTimeImmutable;
use DateTimeInterface;

class OrdenProduccionRepository implements OrdenProduccionRepositoryInterface
{
    /**
     * @var OrdenItemRepository
     */
    public readonly OrdenItemRepository $ordenItemRepository;

    /**
     * @var ItemDespachoRepository
     */
    public readonly ItemDespachoRepository $itemDespachoRepository;

    /**
     * @var ProduccionBatchRepository
     */
    public readonly ProduccionBatchRepository $produccionBatchRepository;

    /**
     * Constructor
     * 
     * @param OrdenItemRepository $ordenItemRepository
     * @param ItemDespachoRepository $itemDespachoRepository
     * @param ProduccionBatchRepository $produccionBatchRepository
     */
    public function __construct(
        OrdenItemRepository $ordenItemRepository,
        ItemDespachoRepository $itemDespachoRepository,
        ProduccionBatchRepository $produccionBatchRepository
    ) {
        $this->ordenItemRepository = $ordenItemRepository;
        $this->itemDespachoRepository = $itemDespachoRepository;
        $this->produccionBatchRepository = $produccionBatchRepository;
    }

    /**
     * @param int|null $id
     * @throws ModelNotFoundException
     * @return AggregateOrdenProduccion|null
     */
    public function byId(int|null $id): ?AggregateOrdenProduccion
    {
        $row = OrdenProduccionModel::query()->with('items')->find($id);

        if (!$row) {
            throw new ModelNotFoundException("La orden de produccion id: {$id} no existe.");
        }

        $fecha = $this->mapDateToDomain($row->fecha);
        $estado = EstadoOP::from($row->estado);
        $items = $this->mapItems($row->items);
        $batches = $this->mapItemsBatches($row->batches);
        $itemsDespacho = $this->mapItemsDespachos($row->despachoItems);

        return AggregateOrdenProduccion::reconstitute(
            $row->id,
            $fecha,
            $row->sucursal_id,
            $estado,
            $items,
            $batches,
            $itemsDespacho
        );
    }

    /**
     * @param AggregateOrdenProduccion $op
     * @param bool $resetItems
     * @param bool $sendOutbox
     * @return int
     */
    public function save(AggregateOrdenProduccion $op, bool $resetItems = false, bool $sendOutbox = false): int
    {
        $model = OrdenProduccionModel::query()->updateOrCreate(
            ['id' => $op->id()],
            [
                'fecha' => $op->fecha()->format('Y-m-d'),
                'sucursal_id' => $op->sucursalId(),
                'estado' => $op->estado()->value
            ]
        );
        $orderId = $model->id;

        if ($resetItems) {
            if ($op->estado()->value == EstadoOP::CREADA->value) {
                OrdenProduccionItemModel::query()->where('op_id', $orderId)->delete();
                $this->savedItems($orderId, $op->items());
            }

            if ($op->estado()->value == EstadoOP::PLANIFICADA->value) {
                ProduccionBatchModel::query()->where('op_id', $orderId)->delete();
                $this->savedBatch($op->batches());
            }

            if ($op->estado()->value == EstadoOP::CERRADA->value) {
                ItemDespachoModel::query()->where('op_id', $orderId)->delete();
                $this->savedDespacho($op->itemsDespacho());
            }
        }

        if ($sendOutbox) {
            $op->publishOutbox($orderId);
        }

        return $orderId;
    }

    /**
     * @param mixed $data
     * @return ModelOrderItems
     */
    private function mapItems($data): ModelOrderItems
    {
        $items = [];

        foreach ($data as $row) {
            $items[] = new OrderItem(
                new Sku(value: $row->product->sku),
                new Qty($row->qty),
                $row->p_id
            );
        }

        return ModelOrderItems::fromArray($items);
    }

    /**
     * @param mixed $data
     * @return AggregateProduccionBatch[]
     */
    private function mapItemsBatches($data): array
    {
        $items = [];

        foreach ($data as $row) {
            $items[] = new AggregateProduccionBatch(
                $row->id,
                $row->op_id,
                $row->p_id,
                $row->estacion_id,
                $row->receta_version_id,
                $row->porcion_id,
                $row->cant_planificada,
                $row->cant_producida,
                $row->merma_gr,
                EstadoPlanificado::from($row->estado),
                $row->rendimiento,
                new Qty($row->qty),
                $row->posicion,
                $row->ruta
            );
        }

        return $items;
    }

    /**
     * @param mixed $data
     * @return ItemDespacho[]
     */
    private function mapItemsDespachos($data): array
    {
        $items = [];

        foreach ($data as $row) {
            $items[] = new ItemDespacho(
                $row->op_id,
                $row->product_id,
                $row->paquete_id
            );
        }

        return $items;
    }

    /**
     * @param int|null $opId
     * @param ModelOrderItems $items
     * @return void
     */
    private function savedItems(int|null $opId, ModelOrderItems $items): void
    {
        foreach ($items as $item) {
            $this->ordenItemRepository->save(
                new AggregateOrdenItem(
                    null,
                    $opId,
                    null,
                    $item->sku()->value(),
                    $item->qty()->value(),
                    0,
                    0
                )
            );
        }
    }

    /**
     * @param int|null $opId
     * @param array $items
     * @return void
     */
    private function savedBatch(array $items): void
    {
        foreach ($items as $key => $item) {
            $this->produccionBatchRepository->save(
                new AggregateProduccionBatch(
                    null,
                    $item->ordenProduccionId,
                    $item->productoId,
                    $item->estacionId,
                    $item->recetaVersionId,
                    $item->porcionId,
                    $item->cantPlanificada,
                    $item->cantProducida,
                    $item->mermaGr,
                    $item->estado,
                    $item->rendimiento,
                    $item->qty,
                    $key + 1
                )
            );
        }
    }

    /**
     * @param array $items
     * @return void
     */
    private function savedDespacho(array $items): void
    {
        foreach ($items as $item) {
            $this->itemDespachoRepository->save(
                new AggregateItemDespacho(
                    $item->ordenProduccionId,
                    $item->productId,
                    null
                )
            );
        }
    }

    /**
     * @param string|DateTimeInterface $value
     * @return DateTimeImmutable
     */
    private function mapDateToDomain(string|DateTimeInterface $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value . ' 00:00:00');
    }
}
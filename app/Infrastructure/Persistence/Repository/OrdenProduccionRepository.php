<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\OrdenProduccion as OrdenProduccionModel;
use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Infrastructure\Persistence\Model\OrderItem as OrdenProduccionItemModel;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Aggregate\OrdenItem as AggregateOrdenItem;
use App\Infrastructure\Persistence\Repository\OrdenItemRepository;
use App\Domain\Produccion\Model\OrderItems as ModelOrderItems;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Aggregate\EstadoPlanificado;
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
     * Constructor
     * 
     * @param OrdenItemRepository $ordenItemRepository
     */
    public function __construct(OrdenItemRepository $ordenItemRepository) {
        $this->ordenItemRepository = $ordenItemRepository;
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
        $items = $this->mapItemsToDomain($row->items);
        $batches = $this->mapItemsBatches($row->batches);

        return AggregateOrdenProduccion::reconstitute(
            $row->id,
            $fecha,
            $row->sucursal_id,
            $estado,
            $items,
            $batches
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
            OrdenProduccionItemModel::query()->where('op_id', $orderId)->delete();
            $this->mapItemsToRows($orderId, $op->items());
        }

        if ($sendOutbox) {
            $op->publishOutbox($orderId);
        }

        return $orderId;
    }

    /** 
     * @return ModelOrderItems
     */
    private function mapItemsToDomain($items): ModelOrderItems
    {
        $domainItems = [];

        foreach ($items as $row) {
            $domainItems[] = new OrderItem(
                new Sku($row->product->sku),
                new Qty($row->qty),
                $row->p_id
            );
        }

        return ModelOrderItems::fromArray($domainItems);
    }

    /**
     * @return AggregateProduccionBatch[]
     */
    private function mapItemsBatches($batches): array
    {
        $domainItems = [];

        foreach ($batches as $row) {
            $domainItems[] = new AggregateProduccionBatch(
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

        return $domainItems;
    }

    /**
     * @param int|null $opId
     * @param ModelOrderItems $items
     * @return void
     */
    private function mapItemsToRows(int|null $opId, ModelOrderItems $items): void
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
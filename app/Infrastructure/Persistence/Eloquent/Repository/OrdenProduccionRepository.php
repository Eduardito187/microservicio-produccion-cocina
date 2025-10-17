<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Infrastructure\Persistence\Eloquent\Model\OrdenProduccion as OrdenProduccionModel;
use App\Infrastructure\Persistence\Eloquent\Model\OrdenItem as OrdenProduccionItemModel;
use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Model\OrderItems as ModelOrderItems;
use App\Infrastructure\Persistence\Eloquent\Repository\ProductRepository;
use App\Domain\Produccion\ValueObject\OrderItem;
use App\Domain\Produccion\Aggregate\EstadoOP;
use App\Domain\Produccion\ValueObject\Qty;
use App\Domain\Produccion\ValueObject\Sku;
use Illuminate\Support\Facades\DB;
use DateTimeImmutable;
use DateTimeInterface;

class OrdenProduccionRepository implements OrdenProduccionRepositoryInterface
{
    /**
     * @var ProductRepository
     */
    public readonly ProductRepository $productRepository;

    /**
     * Constructor
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }
    /**
     * @param int|null $id
     */
    public function byId(int|null $id): ?AggregateOrdenProduccion
    {
        if ($id == null) {
            return null;
        }

        /** @var OrdenProduccionModel|null $row */
        $row = OrdenProduccionModel::query()->with('items')->find($id);

        if (!$row) {
            return null;
        }

        $fecha = $this->mapDateToDomain($row->fecha);
        $estado = EstadoOP::from($row->estado);
        $items = $this->mapItemsToDomain($row->items);

        return AggregateOrdenProduccion::reconstitute(
            $row->id,
            $fecha,
            $row->sucursal_id,
            $estado,
            $items
        );
    }

    /**
     * Persist aggregate and its items (upsert + items sync) in a single transaction.
     */
    public function save(AggregateOrdenProduccion $op): int
    {
        $orderId = 0;

        DB::transaction(function () use ($op, &$orderId) {
            $model = OrdenProduccionModel::query()->updateOrCreate(
                ['id' => $op->id()],
                [
                    'fecha' => $op->fecha()->format('Y-m-d'),
                    'sucursal_id' => $op->sucursalId(),
                    'estado' => $op->estado()->value
                ]
            );
            $orderId = $model->id;
            OrdenProduccionItemModel::query()->where('op_id', $orderId)->delete();
            $toInsert = $this->mapItemsToRows($orderId, $op->items());

            if (!empty($toInsert)) {
                OrdenProduccionItemModel::query()->insert($toInsert);
            }
        });

        return $orderId;
    }

    /** 
     * @return ModelOrderItems
     */
    private function mapItemsToDomain($eloquentItems): ModelOrderItems
    {
        $domainItems = [];

        foreach ($eloquentItems as $row) {
            $domainItems[] = new OrderItem(
                new Sku($row->sku),
                new Qty($row->qty)
            );
        }

        return ModelOrderItems::fromArray($domainItems);
    }

    /**
     * @param int|null $opId
     * @param ModelOrderItems $items
     * @return array
     */
    private function mapItemsToRows(int|null $opId, ModelOrderItems $items): array
    {
        $rows = [];

        foreach ($items as $item) {
            $sku = $item->sku()->value();
            $product = $this->productRepository->bySku($sku);
            $finalPrice = $product->price;

            if ($product->special_price != 0) {
                $finalPrice = $product->special_price;
            }

            $rows[] = [
                'op_id' => $opId,
                'p_id' => $product->id,
                'sku' => $sku,
                'qty' => $item->qty()->value(),
                'price' => $product->price,
                'final_price' => $finalPrice,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        return $rows;
    }

    /**
     * @param string|\DateTimeInterface $value
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
<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Infrastructure\Persistence\Eloquent\Model\OrdenItem as OrdenItemModel;
use App\Domain\Produccion\Repository\OrdenItemRepositoryInterface;
use App\Domain\Produccion\Aggregate\OrdenItem as AggregateOrdenItem;
use DateTimeImmutable;

class OrdenItemRepository implements OrdenItemRepositoryInterface
{
    /**
     * @param string $id
     * @return AggregateOrdenItem|null
     */
    public function byId(string $id): ?AggregateOrdenItem
    {
        $row = OrdenItemModel::find($id);

        if (!$row) return null;

        return new AggregateOrdenItem(
            $row->id,
            $row->ordenProduccionId,
            $row->productId,
            $row->sku,
            $row->qty,
            $row->price,
            $row->finalPrice
        );
    }

    /**
     * @param AggregateOrdenItem $item
     * @return void
     */
    public function save(AggregateOrdenItem $item): void
    {
        OrdenItemModel::updateOrCreate(
            ['id' => $item->id],
            [
                'op_id' => $item->ordenProduccionId,
                'p_id' => $item->productId,
                'sku' => $item->sku,
                'qty' => $item->qty,
                'price' => $item->price,
                'final_price' => $item->finalPrice
            ]
        );
    }
}
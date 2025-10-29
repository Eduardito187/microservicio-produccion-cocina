<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\ItemDespacho as ItemDespachoModel;
use App\Domain\Produccion\Aggregate\ItemDespacho as AggregateItemDespacho;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ItemDespachoRepository implements ItemDespachoRepositoryInterface
{
    /**
     * @param string $id
     * @throws ModelNotFoundException
     * @return AggregateItemDespacho|null
     */
    public function byId(string $id): ?AggregateItemDespacho
    {
        $row = ItemDespachoModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("El item despacho id: {$id} no existe.");
        }

        return new AggregateItemDespacho(
            $row->op_id,
            $row->product_id,
            $row->paquete_id
        );
    }

    /**
     * @param AggregateItemDespacho $item
     * @return void
     */
    public function save(AggregateItemDespacho $item): void
    {
        ItemDespachoModel::updateOrCreate(
            ['id' => null],
            [
                'op_id' => $item->ordenProduccionId,
                'product_id' => $item->productId,
                'paquete_id' => $item->paqueteId
            ]
        );
    }
}
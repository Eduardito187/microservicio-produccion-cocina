<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\ItemDespacho as ItemDespachoModel;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\ItemDespacho;

/**
 * @class ItemDespachoRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class ItemDespachoRepository implements ItemDespachoRepositoryInterface
{
    /**
     * @param string $id
     * @throws ModelNotFoundException
     * @return ItemDespacho|null
     */
    public function byId(string $id): ?ItemDespacho
    {
        $row = ItemDespachoModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("El item despacho id: {$id} no existe.");
        }

        return new ItemDespacho(
            $row->id,
            $row->op_id,
            $row->product_id,
            $row->paquete_id
        );
    }

    /**
     * @param ItemDespacho $item
     * @return void
     */
    public function save(ItemDespacho $item): void
    {
        ItemDespachoModel::updateOrCreate(
            ['id' => $item->id],
            [
                'op_id' => $item->ordenProduccionId,
                'product_id' => $item->productId,
                'paquete_id' => $item->paqueteId
            ]
        );
    }
}

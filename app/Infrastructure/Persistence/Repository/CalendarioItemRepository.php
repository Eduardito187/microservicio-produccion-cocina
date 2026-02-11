<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\CalendarioItem as CalendarioItemModel;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\CalendarioItem;

/**
 * @class CalendarioItemRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class CalendarioItemRepository implements CalendarioItemRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return CalendarioItem|null
     */
    public function byId(string|int $id): ?CalendarioItem
    {
        $row = CalendarioItemModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("El calendario item id: {$id} no existe.");
        }

        return new CalendarioItem(
            $row->id,
            $row->calendario_id,
            $row->item_despacho_id
        );
    }

    /**
     * @param CalendarioItem $item
     * @return int
     */
    public function save(CalendarioItem $item): string
    {
        $model = CalendarioItemModel::query()->updateOrCreate(
            ['id' => $item->id],
            [
                'calendario_id' => $item->calendarioId,
                'item_despacho_id' => $item->itemDespachoId,
            ]
        );
        return $model->id;
    }

    /**
     * @return CalendarioItem[]
     */
    public function list(): array
    {
        $items = [];

        foreach (CalendarioItemModel::query()->orderBy('id')->get() as $row) {
            $items[] = new CalendarioItem(
                $row->id,
                $row->calendario_id,
                $row->item_despacho_id
            );
        }

        return $items;
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete(string|int $id): void
    {
        CalendarioItemModel::query()->whereKey($id)->delete();
    }

    /**
     * @param string|int $calendarioId
     * @return void
     */
    public function deleteByCalendarioId(string|int $calendarioId): void
    {
        CalendarioItemModel::query()->where('calendario_id', $calendarioId)->delete();
    }
}

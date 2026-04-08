<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Entity\CalendarioItem;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Model\CalendarioItem as CalendarioItemModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @class CalendarioItemRepository
 */
class CalendarioItemRepository implements CalendarioItemRepositoryInterface
{
    /**
     * @param  int  $id
     *
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?CalendarioItem
    {
        $row = CalendarioItemModel::find($id);

        if (! $row) {
            throw new EntityNotFoundException("El calendario item id: {$id} no existe.");
        }

        return new CalendarioItem(
            $row->id,
            $row->calendario_id,
            $row->item_despacho_id
        );
    }

    /**
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
     * @param  int  $id
     */
    public function delete(string|int $id): void
    {
        CalendarioItemModel::query()->whereKey($id)->delete();
    }

    public function deleteByCalendarioId(string|int $calendarioId): void
    {
        CalendarioItemModel::query()->where('calendario_id', $calendarioId)->delete();
    }

    public function linkItemsByEntregaId(string $entregaId, ?string $contratoId, string $calendarioId): int
    {
        $query = DB::table('item_despacho')
            ->select('id')
            ->where('entrega_id', $entregaId);

        if (is_string($contratoId) && $contratoId !== '') {
            $query->where(function ($q) use ($contratoId): void {
                $q->whereNull('contrato_id')
                    ->orWhere('contrato_id', $contratoId);
            });
        }

        $linked = 0;
        foreach ($query->pluck('id') as $itemId) {
            if (! is_string($itemId) || $itemId === '') {
                continue;
            }
            $exists = DB::table('calendario_item')
                ->where('calendario_id', $calendarioId)
                ->where('item_despacho_id', $itemId)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('calendario_item')->insert([
                'id' => (string) Str::uuid(),
                'calendario_id' => $calendarioId,
                'item_despacho_id' => $itemId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $linked++;
        }

        return $linked;
    }
}

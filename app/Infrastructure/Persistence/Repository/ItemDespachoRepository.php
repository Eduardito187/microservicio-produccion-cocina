<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Model\ItemDespacho as ItemDespachoModel;
use Illuminate\Support\Facades\DB;

/**
 * @class ItemDespachoRepository
 */
class ItemDespachoRepository implements ItemDespachoRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string $id): ?ItemDespacho
    {
        $row = ItemDespachoModel::find($id);

        if (! $row) {
            throw new EntityNotFoundException("El item despacho id: {$id} no existe.");
        }

        return new ItemDespacho(
            $row->id,
            $row->op_id,
            $row->product_id,
            $row->paquete_id,
            $row->paciente_id,
            $row->direccion_id,
            $row->ventana_entrega_id,
            $row->entrega_id,
            $row->contrato_id,
            $row->driver_id
        );
    }

    public function save(ItemDespacho $item): void
    {
        ItemDespachoModel::updateOrCreate(
            ['id' => $item->id],
            [
                'op_id' => $item->ordenProduccionId,
                'product_id' => $item->productId,
                'paquete_id' => $item->paqueteId,
                'paciente_id' => $item->pacienteId,
                'direccion_id' => $item->direccionId,
                'ventana_entrega_id' => $item->ventanaEntregaId,
                'entrega_id' => is_string($item->entregaId) ? $item->entregaId : null,
                'contrato_id' => is_string($item->contratoId) ? $item->contratoId : null,
                'driver_id' => is_string($item->driverId) ? $item->driverId : null,
            ]
        );
    }

    public function findDeliveryRowsByPaqueteId(string $packageId): array
    {
        return DB::table('item_despacho')
            ->select('id', 'op_id', 'delivery_status', 'entrega_id', 'contrato_id', 'ventana_entrega_id')
            ->where('paquete_id', $packageId)
            ->get()
            ->all();
    }

    public function findBackfillRowsByPaqueteId(string $packageId): array
    {
        return DB::table('item_despacho')
            ->select('id', 'ventana_entrega_id', 'entrega_id', 'contrato_id')
            ->where('paquete_id', $packageId)
            ->get()
            ->all();
    }

    public function updateDeliveryFields(string $id, array $fields): void
    {
        DB::table('item_despacho')->where('id', $id)->update($fields);
    }

    public function updateDeliveryContext(string $id, array $fields): void
    {
        $fields['updated_at'] = now();
        DB::table('item_despacho')->where('id', $id)->update($fields);
    }

    public function countDistinctPaquetesByOpId(string $opId): int
    {
        return (int) DB::table('item_despacho')
            ->where('op_id', $opId)
            ->whereNotNull('paquete_id')
            ->distinct()
            ->count('paquete_id');
    }

    public function countDistinctPaquetesByOpIdAndStatus(string $opId, string $status): int
    {
        return (int) DB::table('item_despacho')
            ->where('op_id', $opId)
            ->whereNotNull('paquete_id')
            ->where('delivery_status', $status)
            ->distinct()
            ->count('paquete_id');
    }

    public function findFirstEntregaIdByOpId(string $opId): ?string
    {
        $value = DB::table('item_despacho')
            ->where('op_id', $opId)
            ->whereNotNull('entrega_id')
            ->orderBy('id')
            ->value('entrega_id');

        return is_string($value) ? $value : null;
    }

    public function findFirstContratoIdByOpId(string $opId): ?string
    {
        $value = DB::table('item_despacho')
            ->where('op_id', $opId)
            ->whereNotNull('contrato_id')
            ->orderBy('id')
            ->value('contrato_id');

        return is_string($value) ? $value : null;
    }

    public function findCalendarioIdByOpId(string $opId): ?string
    {
        $value = DB::table('item_despacho as i')
            ->join('calendario_item as ci', 'ci.item_despacho_id', '=', 'i.id')
            ->where('i.op_id', $opId)
            ->whereNotNull('ci.calendario_id')
            ->orderBy('ci.id')
            ->value('ci.calendario_id');

        return is_string($value) && $value !== '' ? $value : null;
    }
}

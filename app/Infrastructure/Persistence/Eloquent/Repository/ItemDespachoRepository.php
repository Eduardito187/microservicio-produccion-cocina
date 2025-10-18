<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Infrastructure\Persistence\Eloquent\Model\ItemDespacho as ItemDespachoModel;
use App\Domain\Produccion\Aggregate\ItemDespacho as AggregateItemDespacho;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;

class ItemDespachoRepository implements ItemDespachoRepositoryInterface
{
    /**
     * @param string $id
     * @return AggregateItemDespacho|null
     */
    public function byId(string $id): ?AggregateItemDespacho
    {
        $row = ItemDespachoModel::find($id);

        if (!$row) return null;

        return new AggregateItemDespacho(
            $row->id,
            $row->lista_id,
            $row->sku,
            $row->etiqueta_id,
            $row->paciente_id,
            $row->direccion_snapshot,
            $row->ventana_entrega
        );
    }

    /**
     * @param AggregateItemDespacho $item
     * @return void
     */
    public function save(AggregateItemDespacho $item): void
    {
        ItemDespachoModel::updateOrCreate(
            ['id' => $item->id],
            [
                'lista_id' => $item->listaId,
                'sku' => $item->sku,
                'etiqueta_id' => $item->etiquetaId,
                'paciente_id' => $item->pacienteId,
                'direccion_snapshot' => $item->direccionSnapshot,
                'ventana_entrega' => $item->ventanaEntrega,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}
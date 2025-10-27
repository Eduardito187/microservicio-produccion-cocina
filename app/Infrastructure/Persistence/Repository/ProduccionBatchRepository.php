<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\ProduccionBatch as ProduccionBatchModel;
use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Aggregate\EstadoPlanificado;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;

class ProduccionBatchRepository implements ProduccionBatchRepositoryInterface
{
    /**
     * @param int|null $id
     * @throws ModelNotFoundException
     * @return AggregateProduccionBatch|null
     */
    public function byId(int|null $id): ?AggregateProduccionBatch
    {
        $row = ProduccionBatchModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("El batch de produccion id: {$id} no existe.");
        }

        return new AggregateProduccionBatch(
            $row->id,
            $row->op_id,
            $row->estacion_id,
            $row->receta_version_id,
            $row->porcion_id,
            $row->cant_planificada,
            $row->cant_producida,
            $row->merma_gr,
            EstadoPlanificado::from($row->estado),
            new Sku($row->sku),
            new Qty($row->qty),
            $row->posicion,
            $row->ruta
        );
    }

    
    /**
     * @param int|null $ordenProduccionId
     * @return AggregateProduccionBatch[]
     */
    public function byOrderId(int|null $ordenProduccionId): array
    {
        if ($ordenProduccionId == null) {
            return [];
        }

        $batchs = ProduccionBatchModel::where('op_id', $ordenProduccionId)->get();

        if (!$batchs) {
            return [];
        }

        $item = [];

        foreach ($batchs as $row) {
            $item[] = new AggregateProduccionBatch(
                $row->id,
                $row->op_id,
                $row->estacion_id,
                $row->receta_version_id,
                $row->porcion_id,
                $row->cant_planificada,
                $row->cant_producida,
                $row->merma_gr,
                EstadoPlanificado::from($row->estado),
                new Sku($row->sku),
                new Qty($row->qty),
                $row->posicion,
                $row->ruta
            );
        }

        return $item;
    }

    /**
     * @param AggregateProduccionBatch $pb
     * @return int
     */
    public function save(AggregateProduccionBatch $pb): int
    {
        $model = ProduccionBatchModel::query()->updateOrCreate(
            ['id' => $pb->id],
            [
                'op_id' => $pb->ordenProduccionId,
                'estacion_id' => $pb->estacionId,
                'receta_version_id' => $pb->recetaVersionId,
                'porcion_id' => $pb->porcionId,
                'cant_planificada' => $pb->cantPlanificada,
                'cant_producida' => $pb->cantProducida,
                'merma_gr' => $pb->mermaGr,
                'estado' => $pb->estado,
                'sku' => $pb->sku->value(),
                'qty' => $pb->qty->value(),
                'posicion' => $pb->posicion,
                'ruta' => $pb->ruta
            ]
        );

        return $model->id;
    }
}
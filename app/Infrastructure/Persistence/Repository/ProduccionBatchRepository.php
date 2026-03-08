<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Model\ProduccionBatch as ProduccionBatchModel;

/**
 * @class ProduccionBatchRepository
 */
class ProduccionBatchRepository implements ProduccionBatchRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(?string $id): ?AggregateProduccionBatch
    {
        $row = ProduccionBatchModel::find($id);

        if (! $row) {
            throw new EntityNotFoundException("El batch de produccion id: {$id} no existe.");
        }

        return new AggregateProduccionBatch(
            $row->id,
            $row->op_id,
            $row->p_id,
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

    /**
     * @return AggregateProduccionBatch[]
     */
    public function byOrderId(?string $ordenProduccionId): array
    {
        if ($ordenProduccionId == null) {
            return [];
        }

        $batchs = ProduccionBatchModel::where('op_id', $ordenProduccionId)->get();

        if (! $batchs) {
            return [];
        }

        $item = [];

        foreach ($batchs as $row) {
            $item[] = new AggregateProduccionBatch(
                $row->id,
                $row->op_id,
                $row->p_id,
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

        return $item;
    }

    /**
     * @return int
     */
    public function save(AggregateProduccionBatch $pb): string
    {
        $model = ProduccionBatchModel::query()->updateOrCreate(
            ['id' => $pb->id],
            [
                'op_id' => $pb->ordenProduccionId,
                'p_id' => $pb->productoId,
                'porcion_id' => $pb->porcionId,
                'cant_planificada' => $pb->cantPlanificada,
                'cant_producida' => $pb->cantProducida,
                'merma_gr' => $pb->mermaGr,
                'estado' => $pb->estado->value,
                'rendimiento' => $pb->rendimiento,
                'qty' => $pb->qty->value(),
                'posicion' => $pb->posicion,
                'ruta' => $pb->ruta,
            ]
        );

        return $model->id;
    }
}

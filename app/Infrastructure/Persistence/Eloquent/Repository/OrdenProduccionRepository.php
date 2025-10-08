<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Infrastructure\Persistence\Eloquent\Model\OrdenProduccion as OrdenProduccionModel;
use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Repository\OrdenProduccion;
use DateTimeImmutable;

class OrdenProduccionRepository implements OrdenProduccion
{
    public function byId(string $id): ?AggregateOrdenProduccion
    {
        $row = OrdenProduccionModel::find($id);

        if (!$row) return null;

        return new AggregateOrdenProduccion(
            $row->id,
            new DateTimeImmutable($row->fecha),
            $row->sede_id,
            $row->estado
        );
    }

    public function save(AggregateOrdenProduccion $op): void
    {
        OrdenProduccionModel::updateOrCreate(
            ['id' => $op->id],
            ['fecha' => $op->fecha->format('Y-m-d'), 'sede_id' => $op->sucursalId, 'estado' => $op->estado()]
        );
    }
}
<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Infrastructure\Persistence\Eloquent\Model\OrdenProduccion as OrdenProduccionModel;
use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Repository\OrdenProduccion as DomainRepository;
use DateTimeImmutable;

class OrdenProduccionRepository implements DomainRepository
{
    /**
     * @param string $id
     * @return AggregateOrdenProduccion|null
     */
    public function byId(string $id): ?AggregateOrdenProduccion
    {
        $row = OrdenProduccionModel::find($id);

        if (!$row) return null;

        return new AggregateOrdenProduccion(
            $row->id,
            new DateTimeImmutable($row->fecha),
            $row->sucursal_id,
            $row->estado
        );
    }

    /**
     * @param AggregateOrdenProduccion $op
     * @return void
     */
    public function save(AggregateOrdenProduccion $op): void
    {
        OrdenProduccionModel::updateOrCreate(
            ['id' => $op->id],
            ['fecha' => $op->fecha->format('Y-m-d'), 'sucursal_id' => $op->sucursalId, 'estado' => $op->estado()]
        );
    }
}
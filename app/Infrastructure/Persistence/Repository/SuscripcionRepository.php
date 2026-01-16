<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Suscripcion as SuscripcionModel;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Suscripcion;

class SuscripcionRepository implements SuscripcionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Suscripcion|null
     */
    public function byId(int $id): ?Suscripcion
    {
        $row = SuscripcionModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("La suscripcion id: {$id} no existe.");
        }

        return new Suscripcion(
            $row->id,
            $row->nombre
        );
    }

    /**
     * @param Suscripcion $suscripcion
     * @return int
     */
    public function save(Suscripcion $suscripcion): int
    {
        $model = SuscripcionModel::query()->updateOrCreate(
            ['id' => $suscripcion->id],
            [
                'nombre' => $suscripcion->nombre,
            ]
        );

        return $model->id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        SuscripcionModel::query()->whereKey($id)->delete();
    }
}

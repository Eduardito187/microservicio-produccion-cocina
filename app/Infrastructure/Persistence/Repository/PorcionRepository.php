<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Porcion as PorcionModel;
use App\Domain\Produccion\Repository\PorcionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Porcion;

class PorcionRepository implements PorcionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Porcion|null
     */
    public function byId(int $id): ?Porcion
    {
        $row = PorcionModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("La porcion id: {$id} no existe.");
        }

        return new Porcion(
            $row->id,
            $row->nombre,
            $row->peso_gr
        );
    }

    /**
     * @param Porcion $porcion
     * @return int
     */
    public function save(Porcion $porcion): int
    {
        $model = PorcionModel::query()->updateOrCreate(
            ['id' => $porcion->id],
            [
                'nombre' => $porcion->nombre,
                'peso_gr' => $porcion->pesoGr,
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
        PorcionModel::query()->whereKey($id)->delete();
    }
}

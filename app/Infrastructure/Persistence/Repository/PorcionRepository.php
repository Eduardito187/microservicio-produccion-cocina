<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Porcion as PorcionModel;
use App\Domain\Produccion\Repository\PorcionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Porcion;

/**
 * @class PorcionRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class PorcionRepository implements PorcionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Porcion|null
     */
    public function byId(string|int $id): ?Porcion
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
    public function save(Porcion $porcion): string
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
     * @return Porcion[]
     */
    public function list(): array
    {
        $items = [];

        foreach (PorcionModel::query()->orderBy('id')->get() as $row) {
            $items[] = new Porcion(
                $row->id,
                $row->nombre,
                $row->peso_gr
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
        PorcionModel::query()->whereKey($id)->delete();
    }
}

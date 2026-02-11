<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Estacion as EstacionModel;
use App\Domain\Produccion\Repository\EstacionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Estacion;

/**
 * @class EstacionRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class EstacionRepository implements EstacionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Estacion|null
     */
    public function byId(string|int $id): ?Estacion
    {
        $row = EstacionModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("La estacion id: {$id} no existe.");
        }

        return new Estacion(
            $row->id,
            $row->nombre,
            $row->capacidad
        );
    }

    /**
     * @param Estacion $estacion
     * @return int
     */
    public function save(Estacion $estacion): string
    {
        $model = EstacionModel::query()->updateOrCreate(
            ['id' => $estacion->id],
            [
                'nombre' => $estacion->nombre,
                'capacidad' => $estacion->capacidad,
            ]
        );

        return $model->id;
    }

    /**
     * @return Estacion[]
     */
    public function list(): array
    {
        $items = [];

        foreach (EstacionModel::query()->orderBy('id')->get() as $row) {
            $items[] = new Estacion(
                $row->id,
                $row->nombre,
                $row->capacidad
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
        EstacionModel::query()->whereKey($id)->delete();
    }
}

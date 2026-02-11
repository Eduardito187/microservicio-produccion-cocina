<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Direccion as DireccionModel;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Direccion;

/**
 * @class DireccionRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class DireccionRepository implements DireccionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Direccion|null
     */
    public function byId(string|int $id): ?Direccion
    {
        $row = DireccionModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("La direccion id: {$id} no existe.");
        }

        return new Direccion(
            $row->id,
            $row->nombre,
            $row->linea1,
            $row->linea2,
            $row->ciudad,
            $row->provincia,
            $row->pais,
            $row->geo
        );
    }

    /**
     * @param Direccion $direccion
     * @return int
     */
    public function save(Direccion $direccion): string
    {
        $model = DireccionModel::query()->updateOrCreate(
            ['id' => $direccion->id],
            [
                'nombre' => $direccion->nombre,
                'linea1' => $direccion->linea1,
                'linea2' => $direccion->linea2,
                'ciudad' => $direccion->ciudad,
                'provincia' => $direccion->provincia,
                'pais' => $direccion->pais,
                'geo' => $direccion->geo,
            ]
        );
        return $model->id;
    }

    /**
     * @return Direccion[]
     */
    public function list(): array
    {
        $items = [];

        foreach (DireccionModel::query()->orderBy('id')->get() as $row) {
            $items[] = new Direccion(
                $row->id,
                $row->nombre,
                $row->linea1,
                $row->linea2,
                $row->ciudad,
                $row->provincia,
                $row->pais,
                $row->geo
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
        DireccionModel::query()->whereKey($id)->delete();
    }
}

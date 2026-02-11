<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\RecetaVersion as RecetaVersionModel;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\RecetaVersion;

/**
 * @class RecetaVersionRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class RecetaVersionRepository implements RecetaVersionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return RecetaVersion|null
     */
    public function byId(string|int $id): ?RecetaVersion
    {
        $row = RecetaVersionModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("La receta version id: {$id} no existe.");
        }

        return new RecetaVersion(
            $row->id,
            $row->nombre,
            $row->nutrientes,
            $row->ingredientes,
            $row->version
        );
    }

    /**
     * @param RecetaVersion $recetaVersion
     * @return int
     */
    public function save(RecetaVersion $recetaVersion): string
    {
        $model = RecetaVersionModel::query()->updateOrCreate(
            ['id' => $recetaVersion->id],
            [
                'nombre' => $recetaVersion->nombre,
                'nutrientes' => $recetaVersion->nutrientes,
                'ingredientes' => $recetaVersion->ingredientes,
                'version' => $recetaVersion->version,
            ]
        );
        return $model->id;
    }

    /**
     * @return RecetaVersion[]
     */
    public function list(): array
    {
        $items = [];

        foreach (RecetaVersionModel::query()->orderBy('id')->get() as $row) {
            $items[] = new RecetaVersion(
                $row->id,
                $row->nombre,
                $row->nutrientes,
                $row->ingredientes,
                $row->version
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
        RecetaVersionModel::query()->whereKey($id)->delete();
    }
}

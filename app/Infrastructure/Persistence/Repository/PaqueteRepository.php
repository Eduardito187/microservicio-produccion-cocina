<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Paquete as PaqueteModel;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Paquete;

/**
 * @class PaqueteRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class PaqueteRepository implements PaqueteRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Paquete|null
     */
    public function byId(string|int $id): ?Paquete
    {
        $row = PaqueteModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("El paquete id: {$id} no existe.");
        }

        return new Paquete(
            $row->id,
            $row->etiqueta_id,
            $row->ventana_id,
            $row->direccion_id
        );
    }

    /**
     * @param Paquete $paquete
     * @return int
     */
    public function save(Paquete $paquete): string
    {
        $model = PaqueteModel::query()->updateOrCreate(
            ['id' => $paquete->id],
            [
                'etiqueta_id' => $paquete->etiquetaId,
                'ventana_id' => $paquete->ventanaId,
                'direccion_id' => $paquete->direccionId,
            ]
        );
        return $model->id;
    }

    /**
     * @return Paquete[]
     */
    public function list(): array
    {
        $items = [];

        foreach (PaqueteModel::query()->orderBy('id')->get() as $row) {
            $items[] = new Paquete(
                $row->id,
                $row->etiqueta_id,
                $row->ventana_id,
                $row->direccion_id
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
        PaqueteModel::query()->whereKey($id)->delete();
    }
}

<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Paquete as PaqueteModel;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Paquete;

class PaqueteRepository implements PaqueteRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Paquete|null
     */
    public function byId(int $id): ?Paquete
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
    public function save(Paquete $paquete): int
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
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        PaqueteModel::query()->whereKey($id)->delete();
    }
}

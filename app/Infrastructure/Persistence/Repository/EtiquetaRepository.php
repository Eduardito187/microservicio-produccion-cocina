<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Etiqueta as EtiquetaModel;
use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Etiqueta;

class EtiquetaRepository implements EtiquetaRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Etiqueta|null
     */
    public function byId(int $id): ?Etiqueta
    {
        $row = EtiquetaModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("La etiqueta id: {$id} no existe.");
        }

        return new Etiqueta(
            $row->id,
            $row->receta_version_id,
            $row->suscripcion_id,
            $row->paciente_id,
            $row->qr_payload
        );
    }

    /**
     * @param Etiqueta $etiqueta
     * @return int
     */
    public function save(Etiqueta $etiqueta): int
    {
        $model = EtiquetaModel::query()->updateOrCreate(
            ['id' => $etiqueta->id],
            [
                'receta_version_id' => $etiqueta->recetaVersionId,
                'suscripcion_id' => $etiqueta->suscripcionId,
                'paciente_id' => $etiqueta->pacienteId,
                'qr_payload' => $etiqueta->qrPayload,
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
        EtiquetaModel::query()->whereKey($id)->delete();
    }
}

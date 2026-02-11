<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Etiqueta as EtiquetaModel;
use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Etiqueta;

/**
 * @class EtiquetaRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class EtiquetaRepository implements EtiquetaRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Etiqueta|null
     */
    public function byId(string|int $id): ?Etiqueta
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
    public function save(Etiqueta $etiqueta): string
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
     * @return Etiqueta[]
     */
    public function list(): array
    {
        $items = [];

        foreach (EtiquetaModel::query()->orderBy('id')->get() as $row) {
            $items[] = new Etiqueta(
                $row->id,
                $row->receta_version_id,
                $row->suscripcion_id,
                $row->paciente_id,
                $row->qr_payload
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
        EtiquetaModel::query()->whereKey($id)->delete();
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Entity\Etiqueta;
use App\Domain\Produccion\Repository\EtiquetaRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Model\Etiqueta as EtiquetaModel;

/**
 * @class EtiquetaRepository
 */
class EtiquetaRepository implements EtiquetaRepositoryInterface
{
    /**
     * @param  int  $id
     *
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Etiqueta
    {
        $row = EtiquetaModel::find($id);

        if (! $row) {
            throw new EntityNotFoundException("La etiqueta id: {$id} no existe.");
        }

        return new Etiqueta(
            $row->id,
            $row->suscripcion_id,
            $row->paciente_id,
            $row->qr_payload
        );
    }

    /**
     * @return int
     */
    public function save(Etiqueta $etiqueta): string
    {
        $model = EtiquetaModel::query()->updateOrCreate(
            ['id' => $etiqueta->id],
            [
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
                $row->suscripcion_id,
                $row->paciente_id,
                $row->qr_payload
            );
        }

        return $items;
    }

    /**
     * @param  int  $id
     */
    public function delete(string|int $id): void
    {
        EtiquetaModel::query()->whereKey($id)->delete();
    }
}

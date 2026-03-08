<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Model\Suscripcion as SuscripcionModel;

/**
 * @class SuscripcionRepository
 */
class SuscripcionRepository implements SuscripcionRepositoryInterface
{
    /**
     * @param  int  $id
     *
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Suscripcion
    {
        $row = SuscripcionModel::find($id);

        if (! $row) {
            throw new EntityNotFoundException("La suscripcion id: {$id} no existe.");
        }

        return new Suscripcion(
            $row->id,
            $row->nombre,
            $row->paciente_id,
            $row->tipo_servicio,
            $row->fecha_inicio?->format('Y-m-d'),
            $row->fecha_fin?->format('Y-m-d'),
            $row->estado,
            $row->motivo_cancelacion,
            $row->cancelado_at?->format(DATE_ATOM)
        );
    }

    /**
     * @return int
     */
    public function save(Suscripcion $suscripcion): string
    {
        $model = SuscripcionModel::query()->updateOrCreate(
            ['id' => $suscripcion->id],
            [
                'nombre' => $suscripcion->nombre,
                'paciente_id' => $suscripcion->pacienteId,
                'tipo_servicio' => $suscripcion->tipoServicio,
                'fecha_inicio' => $suscripcion->fechaInicio,
                'fecha_fin' => $suscripcion->fechaFin,
                'estado' => $suscripcion->estado,
                'motivo_cancelacion' => $suscripcion->motivoCancelacion,
                'cancelado_at' => $suscripcion->canceladoAt,
            ]
        );

        return $model->id;
    }

    /**
     * @return Suscripcion[]
     */
    public function list(): array
    {
        $items = [];

        foreach (SuscripcionModel::query()->orderBy('id')->get() as $row) {
            $items[] = new Suscripcion(
                $row->id,
                $row->nombre,
                $row->paciente_id,
                $row->tipo_servicio,
                $row->fecha_inicio?->format('Y-m-d'),
                $row->fecha_fin?->format('Y-m-d'),
                $row->estado,
                $row->motivo_cancelacion,
                $row->cancelado_at?->format(DATE_ATOM)
            );
        }

        return $items;
    }

    /**
     * @param  int  $id
     */
    public function delete(string|int $id): void
    {
        SuscripcionModel::query()->whereKey($id)->delete();
    }
}

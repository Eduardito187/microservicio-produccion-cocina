<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Model\Paciente as PacienteModel;

/**
 * @class PacienteRepository
 */
class PacienteRepository implements PacienteRepositoryInterface
{
    /**
     * @param  int  $id
     *
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Paciente
    {
        $row = PacienteModel::find($id);

        if (! $row) {
            throw new EntityNotFoundException("El paciente id: {$id} no existe.");
        }

        return new Paciente(
            $row->id,
            $row->nombre,
            $row->documento,
            $row->suscripcion_id
        );
    }

    /**
     * @return int
     */
    public function save(Paciente $paciente): string
    {
        $model = PacienteModel::query()->updateOrCreate(
            ['id' => $paciente->id],
            [
                'nombre' => $paciente->nombre,
                'documento' => $paciente->documento,
                'suscripcion_id' => $paciente->suscripcionId,
            ]
        );

        return $model->id;
    }

    /**
     * @return Paciente[]
     */
    public function list(): array
    {
        $items = [];

        foreach (PacienteModel::query()->orderBy('id')->get() as $row) {
            $items[] = new Paciente(
                $row->id,
                $row->nombre,
                $row->documento,
                $row->suscripcion_id
            );
        }

        return $items;
    }

    /**
     * @param  int  $id
     */
    public function delete(string|int $id): void
    {
        PacienteModel::query()->whereKey($id)->delete();
    }
}

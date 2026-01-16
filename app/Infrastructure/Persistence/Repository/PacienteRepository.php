<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Paciente as PacienteModel;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Paciente;

class PacienteRepository implements PacienteRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Paciente|null
     */
    public function byId(int $id): ?Paciente
    {
        $row = PacienteModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("El paciente id: {$id} no existe.");
        }

        return new Paciente(
            $row->id,
            $row->nombre,
            $row->documento,
            $row->suscripcion_id
        );
    }

    /**
     * @param Paciente $paciente
     * @return int
     */
    public function save(Paciente $paciente): int
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
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        PacienteModel::query()->whereKey($id)->delete();
    }
}

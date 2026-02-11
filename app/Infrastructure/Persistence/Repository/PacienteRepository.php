<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Paciente as PacienteModel;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Paciente;

/**
 * @class PacienteRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class PacienteRepository implements PacienteRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Paciente|null
     */
    public function byId(string|int $id): ?Paciente
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
     * @param int $id
     * @return void
     */
    public function delete(string|int $id): void
    {
        PacienteModel::query()->whereKey($id)->delete();
    }
}

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
     * @return Paciente[]
     */
    public function byCalendarioId(string $calendarioId): array
    {
        $rows = PacienteModel::query()
            ->whereIn('id', function ($query) use ($calendarioId): void {
                $query->select('item_despacho.paciente_id')
                    ->from('calendario_item')
                    ->join('item_despacho', 'item_despacho.id', '=', 'calendario_item.item_despacho_id')
                    ->where('calendario_item.calendario_id', $calendarioId)
                    ->whereNotNull('item_despacho.paciente_id');
            })
            ->orderBy('id')
            ->get();

        return $this->hydrate($rows);
    }

    /**
     * @return Paciente[]
     */
    public function byVentanaEntregaId(string $ventanaEntregaId): array
    {
        $rows = PacienteModel::query()
            ->where(function ($q) use ($ventanaEntregaId): void {
                $q->whereIn('id', function ($query) use ($ventanaEntregaId): void {
                    $query->select('paciente_id')
                        ->from('item_despacho')
                        ->where('ventana_entrega_id', $ventanaEntregaId)
                        ->whereNotNull('paciente_id');
                })->orWhereIn('id', function ($query) use ($ventanaEntregaId): void {
                    $query->select('etiqueta.paciente_id')
                        ->from('paquete')
                        ->join('etiqueta', 'etiqueta.id', '=', 'paquete.etiqueta_id')
                        ->where('paquete.ventana_id', $ventanaEntregaId)
                        ->whereNotNull('etiqueta.paciente_id');
                });
            })
            ->orderBy('id')
            ->get();

        return $this->hydrate($rows);
    }

    /**
     * @param  int  $id
     */
    public function delete(string|int $id): void
    {
        PacienteModel::query()->whereKey($id)->delete();
    }

    /**
     * @return Paciente[]
     */
    private function hydrate(iterable $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = new Paciente(
                $row->id,
                $row->nombre,
                $row->documento,
                $row->suscripcion_id
            );
        }

        return $items;
    }
}

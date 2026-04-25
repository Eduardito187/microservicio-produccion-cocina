<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Model\Calendario as CalendarioModel;
use App\Infrastructure\Persistence\Model\VentanaEntrega as VentanaEntregaModel;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @class VentanaEntregaRepository
 */
class VentanaEntregaRepository implements VentanaEntregaRepositoryInterface
{
    /**
     * @param  int  $id
     *
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?VentanaEntrega
    {
        $row = VentanaEntregaModel::find($id);

        if (! $row) {
            throw new EntityNotFoundException("La ventana de entrega id: {$id} no existe.");
        }

        return new VentanaEntrega(
            $row->id,
            $this->convertDateTime($row->desde),
            $this->convertDateTime($row->hasta),
            $row->entrega_id,
            $row->contrato_id,
            $row->estado
        );
    }

    /**
     * @return int
     */
    public function save(VentanaEntrega $ventanaEntrega): string
    {
        $model = VentanaEntregaModel::query()->updateOrCreate(
            ['id' => $ventanaEntrega->id],
            [
                'desde' => $ventanaEntrega->desde->format('Y-m-d H:i:s'),
                'hasta' => $ventanaEntrega->hasta->format('Y-m-d H:i:s'),
                'entrega_id' => $ventanaEntrega->entregaId,
                'contrato_id' => $ventanaEntrega->contratoId,
                'estado' => is_int($ventanaEntrega->estado) || is_string($ventanaEntrega->estado) ? (int) $ventanaEntrega->estado : null,
            ]
        );

        return $model->id;
    }

    /**
     * @return VentanaEntrega[]
     */
    public function list(): array
    {
        $items = [];

        foreach (VentanaEntregaModel::query()->orderBy('id')->get() as $row) {
            $items[] = new VentanaEntrega(
                $row->id,
                $this->convertDateTime($row->desde),
                $this->convertDateTime($row->hasta),
                $row->entrega_id,
                $row->contrato_id,
                $row->estado
            );
        }

        return $items;
    }

    /**
     * @return VentanaEntrega[]
     */
    public function listVigentes(): array
    {
        $items = [];

        $query = VentanaEntregaModel::query()
            ->where('hasta', '>=', now())
            ->where(fn ($q) => $q->whereNull('estado')->orWhere('estado', '!=', 0))
            ->orderBy('desde');

        foreach ($query->get() as $row) {
            $items[] = new VentanaEntrega(
                $row->id,
                $this->convertDateTime($row->desde),
                $this->convertDateTime($row->hasta),
                $row->entrega_id,
                $row->contrato_id,
                $row->estado
            );
        }

        return $items;
    }

    public function desactivar(string $id): void
    {
        VentanaEntregaModel::query()->whereKey($id)->update(['estado' => 0]);
    }

    /**
     * @return VentanaEntrega[]
     */
    public function byPacienteId(string $pacienteId): array
    {
        $rows = VentanaEntregaModel::query()
            ->where(function ($q) use ($pacienteId): void {
                $q->whereIn('id', function ($query) use ($pacienteId): void {
                    $query->select('ventana_entrega_id')
                        ->from('item_despacho')
                        ->where('paciente_id', $pacienteId)
                        ->whereNotNull('ventana_entrega_id');
                })->orWhereIn('id', function ($query) use ($pacienteId): void {
                    $query->select('paquete.ventana_id')
                        ->from('paquete')
                        ->join('etiqueta', 'etiqueta.id', '=', 'paquete.etiqueta_id')
                        ->where('etiqueta.paciente_id', $pacienteId)
                        ->whereNotNull('paquete.ventana_id');
                });
            })
            ->orderBy('desde')
            ->get();

        return $this->hydrateRows($rows);
    }

    /**
     * @return VentanaEntrega[]
     */
    public function byCalendarioId(string $calendarioId): array
    {
        $calendario = CalendarioModel::query()
            ->where('id', $calendarioId)
            ->first(['entrega_id', 'contrato_id']);

        if ($calendario === null) {
            return [];
        }

        $query = VentanaEntregaModel::query();
        $hasFilter = false;

        if ($calendario->entrega_id !== null && $calendario->entrega_id !== '') {
            $query->where('entrega_id', $calendario->entrega_id);
            $hasFilter = true;
        }

        if ($calendario->contrato_id !== null && $calendario->contrato_id !== '') {
            $query->where('contrato_id', $calendario->contrato_id);
            $hasFilter = true;
        }

        if (! $hasFilter) {
            return [];
        }

        return $this->hydrateRows($query->orderBy('desde')->get());
    }

    /**
     * @param  int  $id
     */
    public function delete(string|int $id): void
    {
        VentanaEntregaModel::query()->whereKey($id)->delete();
    }

    /**
     * @return VentanaEntrega[]
     */
    private function hydrateRows(iterable $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = new VentanaEntrega(
                $row->id,
                $this->convertDateTime($row->desde),
                $this->convertDateTime($row->hasta),
                $row->entrega_id,
                $row->contrato_id,
                $row->estado
            );
        }

        return $items;
    }

    private function convertDateTime(string|DateTimeInterface $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value);
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Model\Calendario as CalendarioModel;
use App\Infrastructure\Persistence\Model\VentanaEntrega as VentanaEntregaModel;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @class CalendarioRepository
 */
class CalendarioRepository implements CalendarioRepositoryInterface
{
    /**
     * @param  int  $id
     *
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Calendario
    {
        $row = CalendarioModel::find($id);

        if (! $row) {
            throw new EntityNotFoundException("El calendario id: {$id} no existe.");
        }

        return new Calendario(
            $row->id,
            $this->convertDate($row->fecha),
            $row->entrega_id,
            $row->contrato_id,
            $row->estado
        );
    }

    /**
     * @return int
     */
    public function save(Calendario $calendario): string
    {
        $fecha = $calendario->fecha->format('Y-m-d');
        $attributes = [
            'entrega_id' => $calendario->entregaId,
            'contrato_id' => $calendario->contratoId,
            'estado' => is_int($calendario->estado) || is_string($calendario->estado) ? (int) $calendario->estado : null,
        ];

        // Buscar por ID determinístico (entregaId + fecha), NO solo por fecha.
        // Distintos entregaId en la misma fecha producen calendarios separados.
        $model = CalendarioModel::query()->where('id', $calendario->id)->first();
        if ($model !== null) {
            $model->fill($attributes);
            $model->save();

            return $model->id;
        }

        $model = CalendarioModel::query()->create(
            ['id' => $calendario->id, 'fecha' => $fecha] + $attributes
        );

        return $model->id;
    }

    /**
     * @return Calendario[]
     */
    public function list(): array
    {
        $items = [];

        foreach (CalendarioModel::query()->orderBy('id')->get() as $row) {
            $items[] = new Calendario(
                $row->id,
                $this->convertDate($row->fecha),
                $row->entrega_id,
                $row->contrato_id,
                $row->estado
            );
        }

        return $items;
    }

    /**
     * @return Calendario[]
     */
    public function byPacienteId(string $pacienteId): array
    {
        $rows = CalendarioModel::query()
            ->whereIn('id', function ($query) use ($pacienteId): void {
                $query->select('calendario_item.calendario_id')
                    ->from('calendario_item')
                    ->join('item_despacho', 'item_despacho.id', '=', 'calendario_item.item_despacho_id')
                    ->where('item_despacho.paciente_id', $pacienteId)
                    ->whereNotNull('calendario_item.calendario_id');
            })
            ->orderBy('fecha')
            ->get();

        return $this->hydrateRows($rows);
    }

    /**
     * @return Calendario[]
     */
    public function byVentanaEntregaId(string $ventanaEntregaId): array
    {
        $ventana = VentanaEntregaModel::query()
            ->where('id', $ventanaEntregaId)
            ->first(['entrega_id', 'contrato_id']);

        if ($ventana === null) {
            return [];
        }

        $query = CalendarioModel::query();
        $hasFilter = false;

        if ($ventana->entrega_id !== null && $ventana->entrega_id !== '') {
            $query->where('entrega_id', $ventana->entrega_id);
            $hasFilter = true;
        }

        if ($ventana->contrato_id !== null && $ventana->contrato_id !== '') {
            $query->where('contrato_id', $ventana->contrato_id);
            $hasFilter = true;
        }

        if (! $hasFilter) {
            return [];
        }

        return $this->hydrateRows($query->orderBy('fecha')->get());
    }

    /**
     * @return Calendario[]
     */
    public function bySuscripcionId(string $suscripcionId): array
    {
        $rows = CalendarioModel::query()
            ->whereIn('id', function ($query) use ($suscripcionId): void {
                $query->select('calendario_item.calendario_id')
                    ->from('calendario_item')
                    ->join('item_despacho', 'item_despacho.id', '=', 'calendario_item.item_despacho_id')
                    ->join('paciente', 'paciente.id', '=', 'item_despacho.paciente_id')
                    ->where('paciente.suscripcion_id', $suscripcionId)
                    ->whereNotNull('calendario_item.calendario_id');
            })
            ->orderBy('fecha')
            ->get();

        return $this->hydrateRows($rows);
    }

    /**
     * @param  int  $id
     */
    public function delete(string|int $id): void
    {
        CalendarioModel::query()->whereKey($id)->delete();
    }

    /**
     * @return Calendario[]
     */
    private function hydrateRows(iterable $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = new Calendario(
                $row->id,
                $this->convertDate($row->fecha),
                $row->entrega_id,
                $row->contrato_id,
                $row->estado
            );
        }

        return $items;
    }

    private function convertDate(string|DateTimeInterface $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value . ' 00:00:00');
    }
}

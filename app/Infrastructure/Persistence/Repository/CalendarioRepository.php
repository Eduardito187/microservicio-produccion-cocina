<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Model\Calendario as CalendarioModel;
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
     * @param  int  $id
     */
    public function delete(string|int $id): void
    {
        CalendarioModel::query()->whereKey($id)->delete();
    }

    private function convertDate(string|DateTimeInterface $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value . ' 00:00:00');
    }
}

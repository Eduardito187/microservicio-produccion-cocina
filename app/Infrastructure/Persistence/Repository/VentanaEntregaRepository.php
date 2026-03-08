<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
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
     * @param  int  $id
     */
    public function delete(string|int $id): void
    {
        VentanaEntregaModel::query()->whereKey($id)->delete();
    }

    private function convertDateTime(string|DateTimeInterface $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value);
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\VentanaEntrega as VentanaEntregaModel;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\VentanaEntrega;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @class VentanaEntregaRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class VentanaEntregaRepository implements VentanaEntregaRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return VentanaEntrega|null
     */
    public function byId(string|int $id): ?VentanaEntrega
    {
        $row = VentanaEntregaModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("La ventana de entrega id: {$id} no existe.");
        }

        return new VentanaEntrega(
            $row->id,
            $this->convertDateTime($row->desde),
            $this->convertDateTime($row->hasta)
        );
    }

    /**
     * @param VentanaEntrega $ventanaEntrega
     * @return int
     */
    public function save(VentanaEntrega $ventanaEntrega): string
    {
        $model = VentanaEntregaModel::query()->updateOrCreate(
            ['id' => $ventanaEntrega->id],
            [
                'desde' => $ventanaEntrega->desde->format('Y-m-d H:i:s'),
                'hasta' => $ventanaEntrega->hasta->format('Y-m-d H:i:s'),
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
                $this->convertDateTime($row->hasta)
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
        VentanaEntregaModel::query()->whereKey($id)->delete();
    }

    /**
     * @param string|DateTimeInterface $value
     * @return DateTimeImmutable
     */
    private function convertDateTime(string|DateTimeInterface $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value);
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Calendario as CalendarioModel;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Produccion\Entity\Calendario;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @class CalendarioRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class CalendarioRepository implements CalendarioRepositoryInterface
{
    /**
     * @param int $id
     * @throws EntityNotFoundException
     * @return Calendario|null
     */
    public function byId(string|int $id): ?Calendario
    {
        $row = CalendarioModel::find($id);

        if (!$row) {
            throw new EntityNotFoundException("El calendario id: {$id} no existe.");
        }

        return new Calendario(
            $row->id,
            $this->convertDate($row->fecha)
        );
    }

    /**
     * @param Calendario $calendario
     * @return int
     */
    public function save(Calendario $calendario): string
    {
        $model = CalendarioModel::query()->updateOrCreate(
            ['id' => $calendario->id],
            [
                'fecha' => $calendario->fecha->format('Y-m-d'),
            ]
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
                $this->convertDate($row->fecha)
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
        CalendarioModel::query()->whereKey($id)->delete();
    }

    /**
     * @param string|DateTimeInterface $value
     * @return DateTimeImmutable
     */
    private function convertDate(string|DateTimeInterface $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value . ' 00:00:00');
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\CalendarioItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class CalendarioItemRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface CalendarioItemRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws ModelNotFoundException
     * @return CalendarioItem|null
     */
    public function byId(string|int $id): ?CalendarioItem;

    /**
     * @param CalendarioItem $item
     * @return int
     */
    public function save(CalendarioItem $item): string;

    /**
     * @return CalendarioItem[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;

    /**
     * @param string|int $calendarioId
     * @return void
     */
    public function deleteByCalendarioId(string|int $calendarioId): void;
}

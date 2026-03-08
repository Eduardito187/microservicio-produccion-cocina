<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\CalendarioItem;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class CalendarioItemRepositoryInterface
 */
interface CalendarioItemRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?CalendarioItem;

    /**
     * @return int
     */
    public function save(CalendarioItem $item): string;

    /**
     * @return CalendarioItem[]
     */
    public function list(): array;

    public function delete(string|int $id): void;

    public function deleteByCalendarioId(string|int $calendarioId): void;
}

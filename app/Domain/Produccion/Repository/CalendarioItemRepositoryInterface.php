<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\CalendarioItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface CalendarioItemRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return CalendarioItem|null
     */
    public function byId(int $id): ?CalendarioItem;

    /**
     * @param CalendarioItem $item
     * @return int
     */
    public function save(CalendarioItem $item): int;

    /**
     * @return CalendarioItem[]
     */
    public function list(): array;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

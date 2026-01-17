<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\RecetaVersion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface RecetaVersionRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return RecetaVersion|null
     */
    public function byId(int $id): ?RecetaVersion;

    /**
     * @param RecetaVersion $recetaVersion
     * @return int
     */
    public function save(RecetaVersion $recetaVersion): int;

    /**
     * @return RecetaVersion[]
     */
    public function list(): array;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

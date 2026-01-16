<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Etiqueta;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface EtiquetaRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Etiqueta|null
     */
    public function byId(int $id): ?Etiqueta;

    /**
     * @param Etiqueta $etiqueta
     * @return int
     */
    public function save(Etiqueta $etiqueta): int;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

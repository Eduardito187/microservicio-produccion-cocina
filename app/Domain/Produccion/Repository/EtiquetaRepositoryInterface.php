<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Etiqueta;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface EtiquetaRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws ModelNotFoundException
     * @return Etiqueta|null
     */
    public function byId(string|int $id): ?Etiqueta;

    /**
     * @param Etiqueta $etiqueta
     * @return int
     */
    public function save(Etiqueta $etiqueta): string;

    /**
     * @return Etiqueta[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}

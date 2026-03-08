<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Etiqueta;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class EtiquetaRepositoryInterface
 */
interface EtiquetaRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Etiqueta;

    /**
     * @return int
     */
    public function save(Etiqueta $etiqueta): string;

    /**
     * @return Etiqueta[]
     */
    public function list(): array;

    public function delete(string|int $id): void;
}

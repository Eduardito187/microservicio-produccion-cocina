<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class PacienteRepositoryInterface
 */
interface PacienteRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Paciente;

    /**
     * @return int
     */
    public function save(Paciente $paciente): string;

    /**
     * @return Paciente[]
     */
    public function list(): array;

    public function delete(string|int $id): void;
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Paciente;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class PacienteRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface PacienteRepositoryInterface
{
    /**
     * @param string|int $id
     * @throws ModelNotFoundException
     * @return Paciente|null
     */
    public function byId(string|int $id): ?Paciente;

    /**
     * @param Paciente $paciente
     * @return int
     */
    public function save(Paciente $paciente): string;

    /**
     * @return Paciente[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}

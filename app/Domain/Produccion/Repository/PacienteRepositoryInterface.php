<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Paciente;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface PacienteRepositoryInterface
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return Paciente|null
     */
    public function byId(int $id): ?Paciente;

    /**
     * @param Paciente $paciente
     * @return int
     */
    public function save(Paciente $paciente): int;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

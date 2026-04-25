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

    /**
     * Pacientes ligados a un calendario via calendario_item -> item_despacho.paciente_id.
     *
     * @return Paciente[]
     */
    public function byCalendarioId(string $calendarioId): array;

    /**
     * Pacientes ligados a una ventana de entrega via item_despacho.ventana_entrega_id
     * y via etiqueta -> paquete.ventana_id.
     *
     * @return Paciente[]
     */
    public function byVentanaEntregaId(string $ventanaEntregaId): array;

    public function delete(string|int $id): void;
}

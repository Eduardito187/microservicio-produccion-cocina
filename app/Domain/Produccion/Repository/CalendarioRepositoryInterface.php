<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class CalendarioRepositoryInterface
 */
interface CalendarioRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?Calendario;

    /**
     * @return int
     */
    public function save(Calendario $calendario): string;

    /**
     * @return Calendario[]
     */
    public function list(): array;

    /**
     * Calendarios asociados a un paciente via item_despacho -> calendario_item.
     *
     * @return Calendario[]
     */
    public function byPacienteId(string $pacienteId): array;

    /**
     * Calendarios asociados a una ventana de entrega (mismos entrega_id + contrato_id).
     *
     * @return Calendario[]
     */
    public function byVentanaEntregaId(string $ventanaEntregaId): array;

    /**
     * Calendarios asociados a una suscripcion via paciente -> item_despacho -> calendario_item.
     *
     * @return Calendario[]
     */
    public function bySuscripcionId(string $suscripcionId): array;

    public function delete(string|int $id): void;
}

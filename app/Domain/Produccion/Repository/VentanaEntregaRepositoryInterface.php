<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * @class VentanaEntregaRepositoryInterface
 */
interface VentanaEntregaRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function byId(string|int $id): ?VentanaEntrega;

    /**
     * @return int
     */
    public function save(VentanaEntrega $ventanaEntrega): string;

    /**
     * @return VentanaEntrega[]
     */
    public function list(): array;

    /**
     * @return VentanaEntrega[]
     */
    public function listVigentes(): array;

    /**
     * Ventanas de entrega ligadas a un paciente via item_despacho.ventana_entrega_id
     * y via etiqueta -> paquete.ventana_id.
     *
     * @return VentanaEntrega[]
     */
    public function byPacienteId(string $pacienteId): array;

    /**
     * Ventanas de entrega asociadas a un calendario (mismos entrega_id + contrato_id).
     *
     * @return VentanaEntrega[]
     */
    public function byCalendarioId(string $calendarioId): array;

    public function delete(string|int $id): void;
}

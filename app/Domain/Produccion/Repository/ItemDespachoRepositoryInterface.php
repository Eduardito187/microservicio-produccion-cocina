<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\ItemDespacho;

/**
 * @class ItemDespachoRepositoryInterface
 */
interface ItemDespachoRepositoryInterface
{
    public function byId(string $id): ?ItemDespacho;

    public function save(ItemDespacho $item): void;

    /**
     * Retorna filas de item_despacho para el loop de actualizacion de estado de entrega.
     *
     * @return list<object{id:string,op_id:?string,delivery_status:?string,entrega_id:?string,contrato_id:?string,ventana_entrega_id:?string}>
     */
    public function findDeliveryRowsByPaqueteId(string $packageId): array;

    /**
     * Retorna filas de item_despacho para el proceso de backfill de contexto.
     *
     * @return list<object{id:string,ventana_entrega_id:?string,entrega_id:?string,contrato_id:?string}>
     */
    public function findBackfillRowsByPaqueteId(string $packageId): array;

    public function updateDeliveryFields(string $id, array $fields): void;

    public function updateDeliveryContext(string $id, array $fields): void;

    public function countDistinctPaquetesByOpId(string $opId): int;

    public function countDistinctPaquetesByOpIdAndStatus(string $opId, string $status): int;

    public function findFirstEntregaIdByOpId(string $opId): ?string;

    public function findFirstContratoIdByOpId(string $opId): ?string;

    public function findCalendarioIdByOpId(string $opId): ?string;
}

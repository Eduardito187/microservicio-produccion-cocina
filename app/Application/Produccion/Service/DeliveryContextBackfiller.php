<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Service;

use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;

/**
 * Rellena entrega_id y contrato_id en los items de despacho de un paquete
 * consultando la ventana de entrega asociada cuando faltan esos datos.
 *
 * @class DeliveryContextBackfiller
 */
class DeliveryContextBackfiller
{
    /**
     * @var ItemDespachoRepositoryInterface
     */
    private $itemDespachoRepository;

    /**
     * @var VentanaEntregaRepositoryInterface
     */
    private $ventanaEntregaRepository;

    public function __construct(
        ItemDespachoRepositoryInterface $itemDespachoRepository,
        VentanaEntregaRepositoryInterface $ventanaEntregaRepository
    ) {
        $this->itemDespachoRepository = $itemDespachoRepository;
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
    }

    public function backfill(string $packageId): void
    {
        $rows = $this->itemDespachoRepository->findBackfillRowsByPaqueteId($packageId);

        foreach ($rows as $row) {
            $hasEntrega = is_string($row->entrega_id) && $row->entrega_id !== '';
            $hasContrato = is_string($row->contrato_id) && $row->contrato_id !== '';

            if ($hasEntrega && $hasContrato) {
                continue;
            }

            $ventanaId = is_string($row->ventana_entrega_id) ? $row->ventana_entrega_id : null;
            if ($ventanaId === null || $ventanaId === '') {
                continue;
            }

            try {
                $ventana = $this->ventanaEntregaRepository->byId($ventanaId);
            } catch (EntityNotFoundException $e) {
                continue;
            }

            if ($ventana === null) {
                continue;
            }

            $update = [];
            if (! $hasEntrega && is_string($ventana->entregaId) && $ventana->entregaId !== '') {
                $update['entrega_id'] = $ventana->entregaId;
            }
            if (! $hasContrato && is_string($ventana->contratoId) && $ventana->contratoId !== '') {
                $update['contrato_id'] = $ventana->contratoId;
            }

            if ($update !== []) {
                $this->itemDespachoRepository->updateDeliveryContext($row->id, $update);
            }
        }
    }
}

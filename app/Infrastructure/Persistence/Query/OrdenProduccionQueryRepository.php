<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Query;

use App\Application\Produccion\Repository\OrdenProduccionQueryRepositoryInterface;
use App\Infrastructure\Persistence\Model\ItemDespacho as ItemDespachoModel;
use App\Infrastructure\Persistence\Model\OrdenProduccion as OrdenProduccionModel;
use App\Infrastructure\Persistence\Model\OrderDeliveryProgress;
use App\Infrastructure\Persistence\Model\Paciente as PacienteModel;
use App\Infrastructure\Persistence\Model\PackageDeliveryHistory;
use App\Infrastructure\Persistence\Model\PackageDeliveryTracking;
use App\Infrastructure\Persistence\Model\Suscripcion as SuscripcionModel;

/**
 * @class OrdenProduccionQueryRepository
 */
class OrdenProduccionQueryRepository implements OrdenProduccionQueryRepositoryInterface
{
    public function consolidadoPorSuscripcion(string $suscripcionId): ?array
    {
        $suscripcion = SuscripcionModel::find($suscripcionId);

        if (! $suscripcion) {
            return null;
        }

        $pacienteIds = PacienteModel::where('suscripcion_id', $suscripcionId)->pluck('id');
        $opIds = ItemDespachoModel::whereIn('paciente_id', $pacienteIds)
            ->whereNotNull('op_id')
            ->pluck('op_id')
            ->unique()
            ->values()
            ->all();

        return [
            'suscripcion' => [
                'id' => $suscripcion->id,
                'nombre' => $suscripcion->nombre,
            ],
            'ordenes' => $this->cargarOrdenes($opIds),
        ];
    }

    public function porId(string $opId): ?array
    {
        $filas = $this->cargarOrdenes([$opId]);

        return $filas[0] ?? null;
    }

    public function todos(): array
    {
        $ids = OrdenProduccionModel::orderBy('fecha', 'desc')->pluck('id')->all();

        return $this->cargarOrdenes($ids);
    }

    private function cargarOrdenes(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $ordenes = OrdenProduccionModel::whereIn('id', $ids)
            ->with(['items.product', 'batches', 'despachoItems'])
            ->orderBy('fecha', 'desc')
            ->get();

        $progresos = OrderDeliveryProgress::whereIn('op_id', $ids)
            ->get()
            ->keyBy('op_id');

        $paqueteIds = $ordenes
            ->flatMap(fn ($op) => $op->despachoItems->pluck('paquete_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $trackings = PackageDeliveryTracking::whereIn('package_id', $paqueteIds)
            ->get()
            ->keyBy('package_id');

        $historiales = PackageDeliveryHistory::whereIn('package_id', $paqueteIds)
            ->orderBy('occurred_on')
            ->get()
            ->groupBy('package_id');

        return $ordenes
            ->map(fn ($op) => $this->mapOrden($op, $progresos, $trackings, $historiales))
            ->all();
    }

    private function mapOrden($op, $progresos, $trackings, $historiales): array
    {
        $progreso = $progresos->get($op->id);

        return [
            'id' => $op->id,
            'fecha' => $op->fecha,
            'estado' => $op->estado,
            'entrega_completada_at' => $op->entrega_completada_at,
            'items' => $op->items->map(fn ($item) => [
                'id' => $item->id,
                'qty' => $item->qty,
                'price' => $item->price,
                'final_price' => $item->final_price,
                'producto' => $item->product ? [
                    'id' => $item->product->id,
                    'sku' => $item->product->sku,
                    'nombre' => $item->product->nombre,
                ] : null,
            ])->all(),
            'batches' => $op->batches->map(fn ($b) => [
                'id' => $b->id,
                'cant_planificada' => $b->cant_planificada,
                'cant_producida' => $b->cant_producida,
                'merma_gr' => $b->merma_gr,
                'rendimiento' => $b->rendimiento,
                'estado' => $b->estado,
                'posicion' => $b->posicion,
                'receta_id' => $b->receta_id,
            ])->all(),
            'despacho' => $op->despachoItems
                ->map(fn ($d) => $this->mapDespacho($d, $trackings, $historiales))
                ->all(),
            'progreso_entrega' => $progreso ? [
                'total_paquetes' => $progreso->total_packages,
                'completados' => $progreso->completed_packages,
                'pendientes' => $progreso->pending_packages,
                'completado_at' => $progreso->all_completed_at,
                'entrega_id' => $progreso->entrega_id,
                'contrato_id' => $progreso->contrato_id,
            ] : null,
        ];
    }

    private function mapDespacho($d, $trackings, $historiales): array
    {
        $tracking = $d->paquete_id ? $trackings->get($d->paquete_id) : null;
        $historial = [];

        if ($d->paquete_id && $historiales->has($d->paquete_id)) {
            $historial = $historiales->get($d->paquete_id)
                ->map(fn ($h) => [
                    'event_id' => $h->event_id,
                    'status' => $h->received_status,
                    'driver_id' => $h->driver_id,
                    'occurred_on' => $h->occurred_on,
                ])
                ->all();
        }

        return [
            'id' => $d->id,
            'delivery_status' => $d->delivery_status,
            'delivery_occurred_on' => $d->delivery_occurred_on,
            'paciente_id' => $d->paciente_id,
            'direccion_id' => $d->direccion_id,
            'ventana_entrega_id' => $d->ventana_entrega_id,
            'entrega_id' => $d->entrega_id,
            'contrato_id' => $d->contrato_id,
            'driver_id' => $d->driver_id,
            'paquete_id' => $d->paquete_id,
            'tracking' => $tracking ? [
                'status' => $tracking->status,
                'completed_at' => $tracking->completed_at,
                'driver_id' => $tracking->driver_id,
                'historial' => $historial,
            ] : null,
        ];
    }
}

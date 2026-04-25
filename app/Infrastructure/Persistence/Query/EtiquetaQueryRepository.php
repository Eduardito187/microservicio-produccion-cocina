<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Query;

use App\Application\Produccion\Repository\EtiquetaQueryRepositoryInterface;
use App\Infrastructure\Persistence\Model\EntregaEvidencia;
use App\Infrastructure\Persistence\Model\Etiqueta as EtiquetaModel;
use App\Infrastructure\Persistence\Model\PackageDeliveryTracking;

/**
 * @class EtiquetaQueryRepository
 */
class EtiquetaQueryRepository implements EtiquetaQueryRepositoryInterface
{
    private const STORE_GEO = ['lat' => -17.7681816, 'lng' => -63.1822064];

    public function listConDetalles(): array
    {
        $etiquetas = EtiquetaModel::with([
            'paciente',
            'suscripcion',
            'paquete.ventana',
            'paquete.direccion',
            'paquete.itemsDespacho',
        ])->orderBy('created_at', 'desc')->get();

        return $this->enrich($etiquetas);
    }

    public function porId(string $id): ?array
    {
        $etiqueta = EtiquetaModel::with([
            'paciente',
            'suscripcion',
            'paquete.ventana',
            'paquete.direccion',
            'paquete.itemsDespacho',
        ])->find($id);

        if ($etiqueta === null) {
            return null;
        }

        return $this->enrich(collect([$etiqueta]))[0] ?? null;
    }

    private function enrich(\Illuminate\Support\Collection $etiquetas): array
    {
        $paqueteIds = $etiquetas
            ->map(fn ($e) => $e->paquete?->id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $trackings = $paqueteIds
            ? PackageDeliveryTracking::whereIn('package_id', $paqueteIds)->get()->keyBy('package_id')
            : collect();

        $evidencias = $paqueteIds
            ? EntregaEvidencia::whereIn('paquete_id', $paqueteIds)
                ->whereNotNull('foto_url')
                ->orderByDesc('occurred_on')
                ->get()
                ->keyBy('paquete_id')
            : collect();

        return $etiquetas
            ->map(fn ($e) => $this->mapEtiqueta($e, $trackings, $evidencias))
            ->all();
    }

    private function mapEtiqueta($e, $trackings, $evidencias): array
    {
        $paciente = $e->paciente;
        $suscripcion = $e->suscripcion;
        $paquete = $e->paquete;
        $tracking = $paquete ? $trackings->get($paquete->id) : null;
        $evidencia = $paquete ? $evidencias->get($paquete->id) : null;
        $dir = $paquete?->direccion;
        $despacho = $paquete?->itemsDespacho->first();

        return [
            'id' => $e->id,
            'qr_payload' => $e->qr_payload,
            'paciente' => $paciente ? [
                'id' => $paciente->id,
                'nombre' => $paciente->nombre,
                'documento' => $paciente->documento,
            ] : null,
            'suscripcion' => $suscripcion ? [
                'id' => $suscripcion->id,
                'nombre' => $suscripcion->nombre,
            ] : null,
            'paquete' => $paquete ? [
                'id' => $paquete->id,
                'ventana_entrega' => $paquete->ventana ? [
                    'id' => $paquete->ventana->id,
                    'desde' => $paquete->ventana->desde?->format('Y-m-d H:i:s'),
                    'hasta' => $paquete->ventana->hasta?->format('Y-m-d H:i:s'),
                    'estado' => $paquete->ventana->estado,
                    'entrega_id' => $paquete->ventana->entrega_id,
                    'contrato_id' => $paquete->ventana->contrato_id,
                ] : null,
                'direccion' => $dir ? [
                    'id' => $dir->id,
                    'linea1' => $dir->linea1,
                    'linea2' => $dir->linea2,
                    'ciudad' => $dir->ciudad,
                    'provincia' => $dir->provincia,
                    'pais' => $dir->pais,
                    'geo' => $dir->geo,
                ] : null,
                'ruta' => [
                    'origen' => self::STORE_GEO,
                    'destino' => $dir?->geo,
                ],
                'despacho' => $despacho ? [
                    'id' => $despacho->id,
                    'op_id' => $despacho->op_id,
                    'delivery_status' => $despacho->delivery_status,
                    'delivery_occurred_on' => $despacho->delivery_occurred_on,
                    'driver_id' => $despacho->driver_id,
                    'entrega_id' => $despacho->entrega_id,
                    'contrato_id' => $despacho->contrato_id,
                ] : null,
                'tracking' => $tracking ? [
                    'status' => $tracking->status,
                    'completed_at' => $tracking->completed_at,
                    'driver_id' => $tracking->driver_id,
                    'foto_url' => $evidencia?->foto_url,
                    'incident_type' => $evidencia?->incident_type,
                    'incident_description' => $evidencia?->incident_description,
                ] : null,
            ] : null,
            'created_at' => $e->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

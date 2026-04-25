<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Query;

use App\Application\Produccion\Repository\PaqueteQueryRepositoryInterface;
use App\Infrastructure\Persistence\Model\EntregaEvidencia;
use App\Infrastructure\Persistence\Model\PackageDeliveryTracking;
use App\Infrastructure\Persistence\Model\Paquete as PaqueteModel;

/**
 * @class PaqueteQueryRepository
 */
class PaqueteQueryRepository implements PaqueteQueryRepositoryInterface
{
    private const STORE_GEO = ['lat' => -17.7681816, 'lng' => -63.1822064];

    public function listConDetalles(): array
    {
        $paquetes = PaqueteModel::with([
            'etiqueta.paciente',
            'etiqueta.suscripcion',
            'ventana',
            'direccion',
            'itemsDespacho',
        ])->orderBy('created_at', 'desc')->get();

        return $this->enrich($paquetes);
    }

    public function porId(string $id): ?array
    {
        $paquete = PaqueteModel::with([
            'etiqueta.paciente',
            'etiqueta.suscripcion',
            'ventana',
            'direccion',
            'itemsDespacho',
        ])->find($id);

        if ($paquete === null) {
            return null;
        }

        $ids = collect([$paquete]);

        return $this->enrich($ids)[0] ?? null;
    }

    private function enrich(\Illuminate\Support\Collection $paquetes): array
    {
        $ids = $paquetes->pluck('id')->all();

        $trackings = PackageDeliveryTracking::whereIn('package_id', $ids)
            ->get()
            ->keyBy('package_id');

        $evidencias = EntregaEvidencia::whereIn('paquete_id', $ids)
            ->whereNotNull('foto_url')
            ->orderByDesc('occurred_on')
            ->get()
            ->keyBy('paquete_id');

        return $paquetes
            ->map(fn ($p) => $this->mapPaquete($p, $trackings, $evidencias))
            ->all();
    }

    private function mapPaquete($p, $trackings, $evidencias): array
    {
        $tracking = $trackings->get($p->id);
        $evidencia = $evidencias->get($p->id);
        $etiqueta = $p->etiqueta;
        $paciente = $etiqueta?->paciente;
        $suscripcion = $etiqueta?->suscripcion;
        $ventana = $p->ventana;
        $dir = $p->direccion;
        $despacho = $p->itemsDespacho->first();

        return [
            'id' => $p->id,
            'etiqueta' => $etiqueta ? [
                'id' => $etiqueta->id,
                'qr_payload' => $etiqueta->qr_payload,
                'paciente' => $paciente ? [
                    'id' => $paciente->id,
                    'nombre' => $paciente->nombre,
                    'documento' => $paciente->documento,
                ] : null,
                'suscripcion' => $suscripcion ? [
                    'id' => $suscripcion->id,
                    'nombre' => $suscripcion->nombre,
                ] : null,
            ] : null,
            'ventana_entrega' => $ventana ? [
                'id' => $ventana->id,
                'desde' => $ventana->desde?->format('Y-m-d H:i:s'),
                'hasta' => $ventana->hasta?->format('Y-m-d H:i:s'),
                'estado' => $ventana->estado,
                'entrega_id' => $ventana->entrega_id,
                'contrato_id' => $ventana->contrato_id,
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
            'created_at' => $p->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Query;

use App\Infrastructure\Persistence\Model\Calendario as CalendarioModel;

/**
 * @class AgendaQueryRepository
 */
class AgendaQueryRepository
{
    public function consolidada(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $query = CalendarioModel::with([
            'items.itemDespacho.paciente.suscripcion',
            'items.itemDespacho.ventanaEntrega',
        ])->orderBy('fecha');

        if ($fechaInicio) {
            $query->whereDate('fecha', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('fecha', '<=', $fechaFin);
        }

        $agrupado = [];

        foreach ($query->get() as $calendario) {
            $fechaKey = $calendario->fecha->format('Y-m-d');

            foreach ($calendario->items as $item) {
                $despacho = $item->itemDespacho;

                if (! $despacho) {
                    continue;
                }

                $paciente = $despacho->paciente;
                $suscripcion = $paciente?->suscripcion;
                $ventana = $despacho->ventanaEntrega;

                $agrupado[$fechaKey][] = [
                    'calendario_id' => $calendario->id,
                    'estado' => $calendario->estado,
                    'entrega_id' => $calendario->entrega_id,
                    'contrato_id' => $calendario->contrato_id,
                    'paciente' => $paciente ? [
                        'id' => $paciente->id,
                        'nombre' => $paciente->nombre,
                        'documento' => $paciente->documento,
                    ] : null,
                    'suscripcion' => $suscripcion ? [
                        'id' => $suscripcion->id,
                        'nombre' => $suscripcion->nombre,
                    ] : null,
                    'ventana_entrega' => $ventana ? [
                        'id' => $ventana->id,
                        'desde' => $ventana->desde,
                        'hasta' => $ventana->hasta,
                    ] : null,
                ];
            }
        }

        return $agrupado;
    }
}

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

        $query->whereDate('fecha', '>=', $fechaInicio ?? now()->toDateString());

        if ($fechaFin) {
            $query->whereDate('fecha', '<=', $fechaFin);
        }

        $agrupado = [];

        foreach ($query->get() as $calendario) {
            $fechaKey = $calendario->fecha->format('Y-m-d');
            $this->agregarEntradasCalendario($agrupado, $fechaKey, $calendario);
        }

        return $agrupado;
    }

    private function agregarEntradasCalendario(array &$agrupado, string $fechaKey, object $calendario): void
    {
        if ($calendario->items->isEmpty()) {
            $agrupado[$fechaKey][] = $this->entradaSinDespacho($calendario);

            return;
        }

        foreach ($calendario->items as $item) {
            $agrupado[$fechaKey][] = $this->entradaDesdeItem($calendario, $item);
        }
    }

    private function entradaDesdeItem(object $calendario, object $item): array
    {
        $despacho = $item->itemDespacho;

        if (! $despacho) {
            return $this->entradaSinDespacho($calendario);
        }

        $paciente = $despacho->paciente;
        $suscripcion = $paciente?->suscripcion;
        $ventana = $despacho->ventanaEntrega;

        return [
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

    private function entradaSinDespacho(object $calendario): array
    {
        return [
            'calendario_id' => $calendario->id,
            'estado' => $calendario->estado,
            'entrega_id' => $calendario->entrega_id,
            'contrato_id' => $calendario->contrato_id,
            'paciente' => null,
            'suscripcion' => null,
            'ventana_entrega' => null,
        ];
    }
}

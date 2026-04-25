<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Model\VentanaEntrega as VentanaEntregaModel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Retorna la próxima ventana de entrega según hora de Bolivia (America/La_Paz, UTC-4).
 *
 * @class ObtenerProximaVentanaEntregaController
 */
class ObtenerProximaVentanaEntregaController
{
    private const TZ = 'America/La_Paz';

    public function __invoke(Request $request): JsonResponse
    {
        $ahora = Carbon::now(self::TZ);

        $ventana = VentanaEntregaModel::where('hasta', '>', $ahora)
            ->orderBy('desde', 'asc')
            ->first();

        if ($ventana === null) {
            return response()->json(['message' => 'No hay ventanas de entrega próximas.'], 404);
        }

        return response()->json([
            'id' => $ventana->id,
            'desde' => Carbon::parse($ventana->desde)->setTimezone(self::TZ)->format('Y-m-d H:i:s'),
            'hasta' => Carbon::parse($ventana->hasta)->setTimezone(self::TZ)->format('Y-m-d H:i:s'),
            'estado' => $ventana->estado,
            'entrega_id' => $ventana->entrega_id,
            'contrato_id' => $ventana->contrato_id,
        ]);
    }
}

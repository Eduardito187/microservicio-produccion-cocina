<?php

namespace App\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PactStateController
{
    public function __invoke(Request $request): JsonResponse
    {
        $state = (string) $request->input('state', '');
        $params = (array) $request->input('params', []);

        if ($state === '') {
            return response()->json(['ok' => false, 'error' => 'Missing state'], 400);
        }

        try {
            DB::beginTransaction();

            switch ($state) {
                case 'product SKU1 exists':
                    $this->ensureProductSku1();
                    break;

                case 'orden produccion 1 exists and porcion 1 exists':
                    $this->ensureOrdenAndPorcion();
                    break;

                default:
                    // no-op: no queremos que Pact falle por un state desconocido
                    Log::warning('[PACT_SETUP] Unknown state', ['state' => $state, 'params' => $params]);
                    break;
            }

            DB::commit();
            return response()->json(['ok' => true, 'state' => $state], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[PACT_SETUP_ERROR] '.$state, [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok' => false,
                'state' => $state,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function ensureProductSku1(): void
    {
        $table = 'products';
        DB::table($table)->updateOrInsert(
            ['sku' => 'SKU1'],
            [
                'sku' => 'SKU1',
                'price' => 100,
                'special_price' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    private function ensureOrdenAndPorcion(): void
    {
        // Porcion / porciones
        $porcionTable = null;
        foreach (['porciones', 'porcion'] as $t) {
            if (Schema::hasTable($t)) { $porcionTable = $t; break; }
        }
        if ($porcionTable) {
            $cols = Schema::getColumnListing($porcionTable);
            $row = [];
            if (in_array('id', $cols, true)) $row['id'] = 1;
            if (in_array('nombre', $cols, true)) $row['nombre'] = 'porcion_test';
            if (in_array('created_at', $cols, true)) $row['created_at'] = now();
            if (in_array('updated_at', $cols, true)) $row['updated_at'] = now();
            DB::table($porcionTable)->updateOrInsert(['id' => 1], $row);
        }

        // Orden produccion
        $ordenTable = null;
        foreach (['orden_produccion', 'ordenes_produccion', 'ordenes_produccion'] as $t) {
            if (Schema::hasTable($t)) { $ordenTable = $t; break; }
        }
        if ($ordenTable) {
            $cols = Schema::getColumnListing($ordenTable);
            $row = [];
            if (in_array('id', $cols, true)) $row['id'] = 1;
            if (in_array('estado', $cols, true)) $row['estado'] = 'CREADA';
            if (in_array('sucursal_id', $cols, true)) $row['sucursal_id'] = 'SCZ';
            if (in_array('fecha', $cols, true)) $row['fecha'] = now()->toDateString();
            if (in_array('created_at', $cols, true)) $row['created_at'] = now();
            if (in_array('updated_at', $cols, true)) $row['updated_at'] = now();
            DB::table($ordenTable)->updateOrInsert(['id' => 1], $row);
        }
    }
}
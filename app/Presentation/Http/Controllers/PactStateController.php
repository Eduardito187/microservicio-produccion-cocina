<?php

namespace App\Presentation\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PactStateController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $state = $request->input('state', '');
        $params = $request->input('params', $request->input('providerStateParams', []));
        if (!is_array($params)) {
            $params = [];
        }
        if ($params === []) {
            $params = $request->only([
                'ordenProduccionId',
                'porcionId',
                'estacionId',
                'recetaVersionId',
                'productId'
            ]);
        }

        if ($state === '') {
            return response()->json(['ok' => false, 'error' => 'Missing state'], 400);
        }

        try {
            Log::info('[PACT_SETUP] State received', ['state' => $state, 'params' => $params]);
            DB::beginTransaction();
            switch ($state) {
                case 'product PIZZA-PEP exists':
                    $this->ensureProductSku1();
                    break;
                case 'orden produccion 1 exists and porcion 1 exists':
                    $this->ensureOrdenAndPorcion($params);
                    break;
                default:
                    Log::warning('[PACT_SETUP] Unknown state', ['state' => $state, 'params' => $params]);
                    break;
            }

            DB::commit();
            return response()->json(['ok' => true, 'state' => $state], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[ ] '.$state, ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['ok' => false, 'state' => $state, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @return void
     */
    private function ensureProductSku1(): void
    {
        $productTable = "products";
        $existingProductId = DB::table($productTable)->where('sku', 'PIZZA-PEP')->value('id');
        $productId = $existingProductId ?: (string) Str::uuid();

        DB::table($productTable)->updateOrInsert(
            ['id' => $productId],
            [
                'sku' => 'PIZZA-PEP',
                'price' => 100,
                'special_price' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * @return void
     */
    private function ensureOrdenAndPorcion(array $params): void
    {
        $porcionTable = "porcion";
        $cols = Schema::getColumnListing($porcionTable);
        $row = [];
        $porcionId = $params['porcionId'] ?? $params['porcion_id'] ?? (string) Str::uuid();
        DB::table($porcionTable)->where('nombre', 'porcion_test')->delete();
        if (in_array('id', $cols, true)) $row['id'] = $porcionId;
        if (in_array('nombre', $cols, true)) $row['nombre'] = 'porcion_test';
        if (in_array('peso_gr', $cols, true)) $row['peso_gr'] = 1;
        if (in_array('created_at', $cols, true)) $row['created_at'] = now();
        if (in_array('updated_at', $cols, true)) $row['updated_at'] = now();
        DB::table($porcionTable)->updateOrInsert(['id' => $porcionId], $row);

        $recetaTable = "receta_version";
        $cols = Schema::getColumnListing($recetaTable);
        $row = [];
        $recetaVersionId = $params['recetaVersionId'] ?? $params['receta_version_id'] ?? (string) Str::uuid();
        DB::table($recetaTable)->where('nombre', 'receta_version_test')->delete();
        if (in_array('id', $cols, true)) $row['id'] = $recetaVersionId;
        if (in_array('nombre', $cols, true)) $row['nombre'] = 'receta_version_test';
        if (in_array('version', $cols, true)) $row['version'] = 1;
        if (in_array('created_at', $cols, true)) $row['created_at'] = now();
        if (in_array('updated_at', $cols, true)) $row['updated_at'] = now();
        DB::table($recetaTable)->updateOrInsert(['id' => $recetaVersionId], $row);

        $estacionTable = "estacion";
        $cols = Schema::getColumnListing($estacionTable);
        $row = [];
        $estacionId = $params['estacionId'] ?? $params['estacion_id'] ?? (string) Str::uuid();
        DB::table($estacionTable)->where('nombre', 'estacion_test')->delete();
        if (in_array('id', $cols, true)) $row['id'] = $estacionId;
        if (in_array('nombre', $cols, true)) $row['nombre'] = 'estacion_test';
        if (in_array('capacidad', $cols, true)) $row['capacidad'] = 10;
        if (in_array('created_at', $cols, true)) $row['created_at'] = now();
        if (in_array('updated_at', $cols, true)) $row['updated_at'] = now();
        DB::table($estacionTable)->updateOrInsert(['id' => $estacionId], $row);

        $productTable = "products";
        $cols = Schema::getColumnListing($productTable);
        $existingProductId = DB::table($productTable)->where('sku', 'PIZZA-PEP')->value('id');
        $productId = $existingProductId ?: ($params['productId'] ?? $params['product_id'] ?? (string) Str::uuid());
        $row = [];
        if (in_array('id', $cols, true)) $row['id'] = $productId;
        if (in_array('sku', $cols, true)) $row['sku'] = 'PIZZA-PEP';
        if (in_array('price', $cols, true)) $row['price'] = 100;
        if (in_array('special_price', $cols, true)) $row['special_price'] = 0;
        if (in_array('created_at', $cols, true)) $row['created_at'] = now();
        if (in_array('updated_at', $cols, true)) $row['updated_at'] = now();
        DB::table($productTable)->updateOrInsert(['id' => $productId], $row);

        $orderTable = "orden_produccion";
        $cols = Schema::getColumnListing($orderTable);
        $row = [];
        $ordenId = $params['ordenProduccionId'] ?? $params['orden_produccion_id'] ?? (string) Str::uuid();
        if (in_array('id', $cols, true)) $row['id'] = $ordenId;
        if (in_array('estado', $cols, true)) $row['estado'] = 'CREADA';
        if (in_array('sucursal_id', $cols, true)) $row['sucursal_id'] = 'SCZ';
        if (in_array('fecha', $cols, true)) $row['fecha'] = now()->toDateString();
        if (in_array('created_at', $cols, true)) $row['created_at'] = now();
        if (in_array('updated_at', $cols, true)) $row['updated_at'] = now();
        DB::table($orderTable)->updateOrInsert(['id' => $ordenId], $row);

        $orderItemTable = "order_item";
        $cols = Schema::getColumnListing($orderItemTable);
        $row = [];
        $orderItemId = (string) Str::uuid();
        if (in_array('id', $cols, true)) $row['id'] = $orderItemId;
        if (in_array('op_id', $cols, true)) $row['op_id'] = $ordenId;
        if (in_array('p_id', $cols, true)) $row['p_id'] = $productId;
        if (in_array('qty', $cols, true)) $row['qty'] = 1;
        if (in_array('price', $cols, true)) $row['price'] = 100;
        if (in_array('final_price', $cols, true)) $row['final_price'] = 100;
        if (in_array('created_at', $cols, true)) $row['created_at'] = now();
        if (in_array('updated_at', $cols, true)) $row['updated_at'] = now();
        DB::table($orderItemTable)->updateOrInsert(['id' => $orderItemId], $row);
    }
}




<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @class PactStateController
 * @package App\Presentation\Http\Controllers
 */
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
        if ($params === []) {
            $params = [
                'ordenProduccionId' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
                'estacionId' => '9b7b5fbe-6b65-4d1d-8fdd-52f143b2552f',
                'recetaVersionId' => 'a99e307d-3ca9-44a7-a475-42f0f86d7d04',
                'porcionId' => 'f7a1e0b2-2c4d-4c0a-9b8e-0a4b2f9d8f7a',
            ];
        }

        $porcionTable = "porcion";
        $row = [];
        $porcionId = $params['porcionId'] ?? $params['porcion_id'] ?? (string) Str::uuid();
        $existingPorcionId = DB::table($porcionTable)->where('nombre', 'porcion_test')->value('id');
        if ($existingPorcionId && $existingPorcionId !== $porcionId) {
            DB::table('produccion_batch')->where('porcion_id', $existingPorcionId)->delete();
            DB::table($porcionTable)->where('id', $existingPorcionId)->delete();
        }
        $row['id'] = $porcionId;
        $row['nombre'] = 'porcion_test';
        $row['peso_gr'] = 1;
        $row['created_at'] = now();
        $row['updated_at'] = now();
        DB::table($porcionTable)->updateOrInsert(['id' => $porcionId], $row);

        $recetaTable = "receta_version";
        $row = [];
        $recetaVersionId = $params['recetaVersionId'] ?? $params['receta_version_id'] ?? (string) Str::uuid();
        $existingRecetaId = DB::table($recetaTable)->where('nombre', 'receta_version_test')->value('id');
        if ($existingRecetaId && $existingRecetaId !== $recetaVersionId) {
            DB::table('produccion_batch')->where('receta_version_id', $existingRecetaId)->delete();
            DB::table($recetaTable)->where('id', $existingRecetaId)->delete();
        }
        $row['id'] = $recetaVersionId;
        $row['nombre'] = 'receta_version_test';
        $row['version'] = 1;
        $row['created_at'] = now();
        $row['updated_at'] = now();
        DB::table($recetaTable)->updateOrInsert(['id' => $recetaVersionId], $row);

        $estacionTable = "estacion";
        $row = [];
        $estacionId = $params['estacionId'] ?? $params['estacion_id'] ?? (string) Str::uuid();
        $existingEstacionId = DB::table($estacionTable)->where('nombre', 'estacion_test')->value('id');
        if ($existingEstacionId && $existingEstacionId !== $estacionId) {
            DB::table('produccion_batch')->where('estacion_id', $existingEstacionId)->delete();
            DB::table($estacionTable)->where('id', $existingEstacionId)->delete();
        }
        $row['id'] = $estacionId;
        $row['nombre'] = 'estacion_test';
        $row['capacidad'] = 10;
        $row['created_at'] = now();
        $row['updated_at'] = now();
        DB::table($estacionTable)->updateOrInsert(['id' => $estacionId], $row);

        $productTable = "products";
        $existingProductId = DB::table($productTable)->where('sku', 'PIZZA-PEP')->value('id');
        $productId = $existingProductId ?: ($params['productId'] ?? $params['product_id'] ?? (string) Str::uuid());
        $row = [];
        $row['id'] = $productId;
        $row['sku'] = 'PIZZA-PEP';
        $row['price'] = 100;
        $row['special_price'] = 0;
        $row['created_at'] = now();
        $row['updated_at'] = now();
        DB::table($productTable)->updateOrInsert(['id' => $productId], $row);

        $orderTable = "orden_produccion";
        $row = [];
        $ordenId = $params['ordenProduccionId'] ?? $params['orden_produccion_id'] ?? (string) Str::uuid();
        DB::table('produccion_batch')->where('op_id', $ordenId)->delete();
        DB::table('order_item')->where('op_id', $ordenId)->delete();
        $row['id'] = $ordenId;
        $row['estado'] = 'CREADA';
        $row['sucursal_id'] = 'SCZ';
        $row['fecha'] = now()->toDateString();
        $row['created_at'] = now();
        $row['updated_at'] = now();
        DB::table($orderTable)->updateOrInsert(['id' => $ordenId], $row);

        $orderItemTable = "order_item";
        $row = [];
        $orderItemId = (string) Str::uuid();
        $row['id'] = $orderItemId;
        $row['op_id'] = $ordenId;
        $row['p_id'] = $productId;
        $row['qty'] = 1;
        $row['price'] = 100;
        $row['final_price'] = 100;
        $row['created_at'] = now();
        $row['updated_at'] = now();
        DB::table($orderItemTable)->updateOrInsert(['id' => $orderItemId], $row);
    }
}

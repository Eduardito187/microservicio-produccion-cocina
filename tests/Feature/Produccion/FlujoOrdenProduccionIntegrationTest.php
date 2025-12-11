<?php

namespace Tests\Feature\Produccion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;

class FlujoOrdenProduccionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @inheritDoc
     */
    public function test_flujo_completo_generar_planificar_procesar_despachar(): void
    {
        $now = Carbon::now('America/La_Paz');
        $today = $now->copy()->startOfDay();

        // 🔧 Arrange: crear datos mínimos que tus endpoints requieren
        // Ajusta estos inserts a tus tablas reales (nombres y columnas).
        $estacionId = DB::table('estacion')->insertGetId([
            'nombre' => 'Estación 1',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $recetaVersion1Id = DB::table('receta_version')->insertGetId([
            'nombre' => 'Pizza Pepperoni v1',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $recetaVersion2Id = DB::table('receta_version')->insertGetId([
            'nombre' => 'Pizza Margarita v2',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $porcionId = DB::table('porcion')->insertGetId([
            'nombre' => 'Porción estándar',
            'peso_gr' => 50,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $pacienteId = DB::table('paciente')->insertGetId([
            'nombre' => 'Paciente Demo',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $ventanaEntregaId = DB::table('ventana_entrega')->insertGetId([
            'desde' => $today->copy()->setTime(8, 0, 0),
            'hasta' => $today->copy()->setTime(12, 0, 0),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $direccionId = DB::table('direccion')->insertGetId([
            'nombre' => 'Test',
            'linea1' => 'Test',
            'linea2' => 'Test',
            'ciudad' => 'Test',
            'provincia' => 'Test',
            'pais' => 'Test',
            'geo' => json_encode(['latitud' => -16.4990100, 'longitud' => -68.1462480]),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $suscripcionId = DB::table('suscripcion')->insertGetId([
            'nombre' => 'Suscriptcion Demo',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $etiquetaId = DB::table('etiqueta')->insertGetId([
            'receta_version_id' => $recetaVersion1Id,
            'suscripcion_id' => $suscripcionId,
            'paciente_id' => $pacienteId,
            'qr_payload' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $direccionId = DB::table('paquete')->insertGetId([
            'etiqueta_id' => $etiquetaId,
            'ventana_id' => $ventanaEntregaId,
            'direccion_id' => $direccionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 🔗 URLs de las rutas usando nombres
        $urlGenerar    = route('produccion.ordenes.generar');
        $urlPlanificar = route('produccion.ordenes.planificar');
        $urlProcesar   = route('produccion.ordenes.procesar');
        $urlDespachar  = route('produccion.ordenes.despachar');

        // 1️⃣ GENERAR ORDEN
        $payloadGenerar = [
            "fecha" => "2025-11-04",
            "sucursalId" => "SCZ-001",
            "items" => [
                ["sku" => "PIZZA-PEP", "qty" => 1],
                ["sku" => "PIZZA-MARG", "qty" => 1]
            ],
        ];

        $responseGenerar = $this->postJson($urlGenerar, $payloadGenerar);
        $responseGenerar->assertCreated()->assertJsonStructure(['ordenProduccionId']);
        $opId = $responseGenerar->json('ordenProduccionId');

        // Assert en DB: orden creada
        $this->assertDatabaseHas('orden_produccion', [
            'id' => $opId,
            'sucursal_id' => 'SCZ-001',
            'estado' => 'CREADA'
        ]);

        // Assert en DB: items creados
        $this->assertDatabaseHas('order_item', [
            'orden_produccion_id' => $opId,
            'sku' => 'PIZZA-PEP',
            'qty' => 1
        ]);
        $this->assertDatabaseHas('order_item', [
            'orden_produccion_id' => $opId,
            'sku' => 'PIZZA-MARG',
            'qty' => 1
        ]);

        // 2️⃣ PLANIFICAR ORDEN
        $payloadPlanificar = [
            "ordenProduccionId" => $opId,
            "estacionId" => $estacionId,
            "recetaVersionId" => $recetaVersion1Id,
            "porcionId" => $porcionId
        ];

        $responsePlanificar = $this->postJson($urlPlanificar, $payloadPlanificar);
        $responsePlanificar->assertOk()->assertJson(['status' => 'OK']);

        // Assert: orden en estado PLANIFICADA (ajusta si usas otro nombre)
        $this->assertDatabaseHas('orden_produccion', [
            'id' => $opId,
            'estado' => 'PLANIFICADA'
        ]);

        // 3️⃣ PROCESAR ORDEN
        $payloadProcesar = ["ordenProduccionId" => $opId];
        $responseProcesar = $this->postJson($urlProcesar, $payloadProcesar);
        $responseProcesar->assertOk()->assertJson(['status' => 'OK']);

        // Assert: orden en estado EN_PROCESO (o el que use tu dominio)
        $this->assertDatabaseHas('orden_produccion', [
            'id' => $opId,
            'estado' => 'EN_PROCESO'
        ]);

        // 4️⃣ DESPACHAR ORDEN
        $payloadDespachar = [
            "ordenProduccionId" => $opId,
            "itemsDespacho" => [
                ["sku" => "PIZZA-PEP", "recetaVersionId" => $recetaVersion2Id],
                ["sku" => "PIZZA-MARG", "recetaVersionId" => $recetaVersion1Id]
            ],
            "pacienteId" => $pacienteId,
            "direccionId" => $direccionId,
            "ventanaEntrega"=> $ventanaEntregaId
        ];

        $responseDespachar = $this->postJson($urlDespachar, $payloadDespachar);
        $responseDespachar->assertOk()->assertJson(['status' => 'OK']);

        // Assert: orden despachada / cerrada
        $this->assertDatabaseHas('orden_produccion', [
            'id' => $opId,
            'estado' => 'DESPACHADA'
        ]);

        // Assert: registros de despacho (ajusta nombre de tabla)
        $this->assertDatabaseHas('orden_despacho_items', [
            'orden_produccion_id' => $opId,
            'sku' => 'PIZZA-PEP',
            'receta_version_id' => $recetaVersion2Id
        ]);
        $this->assertDatabaseHas('orden_despacho_items', [
            'orden_produccion_id' => $opId,
            'sku' => 'PIZZA-MARG',
            'receta_version_id' => $recetaVersion1Id
        ]);
    }
}
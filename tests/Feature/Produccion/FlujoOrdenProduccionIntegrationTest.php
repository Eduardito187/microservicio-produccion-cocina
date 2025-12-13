<?php

namespace Tests\Feature\Produccion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;

class FlujoOrdenProduccionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_flujo_completo_generar_planificar_procesar_despachar(): void
    {
        $this->seed();

        $estacionId = DB::table('estacion')->insertGetId([
            'nombre' => 'Estación 1', 'created_at' => now(), 'updated_at' => now()
        ]);
        $recetaVersion1Id = DB::table('receta_version')->insertGetId([
            'nombre' => 'Pizza Pepperoni v1.0', 'created_at' => now(), 'updated_at' => now()
        ]);
        $recetaVersion2Id = DB::table('receta_version')->insertGetId([
            'nombre' => 'Pizza Margarita v2.0', 'created_at' => now(), 'updated_at' => now()
        ]);
        $porcionId = DB::table('porcion')->insertGetId([
            'nombre' => 'Porción estándar', 'peso_gr' => 50, 'created_at' => now(), 'updated_at' => now()
        ]);
        $pacienteId = DB::table('paciente')->insertGetId([
            'nombre' => 'Paciente Demo', 'created_at' => now(), 'updated_at' => now()
        ]);
        $ventanaEntregaId = DB::table('ventana_entrega')->insertGetId([
            'desde' => now(), 'hasta' => now(), 'created_at' => now(), 'updated_at' => now()
        ]);
        $direccionId = DB::table('direccion')->insertGetId([
            'nombre' => 'Test',
            'linea1' => 'Test',
            'linea2' => 'Test',
            'ciudad' => 'Test',
            'provincia' => 'Test',
            'pais' => 'Test',
            'geo' => json_encode(['latitud' => -16.49, 'longitud' => -68.14]),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $suscripcionId = DB::table('suscripcion')->insertGetId([
            'nombre' => 'Suscripción Demo', 'created_at' => now(), 'updated_at' => now()
        ]);

        $etiquetaId = DB::table('etiqueta')->insertGetId([
            'receta_version_id' => $recetaVersion1Id,
            'suscripcion_id' => $suscripcionId,
            'paciente_id' => $pacienteId,
            'qr_payload' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('paquete')->insert([
            'etiqueta_id' => $etiquetaId, 'ventana_id' => $ventanaEntregaId, 'direccion_id' => $direccionId, 'created_at' => now(), 'updated_at' => now()
        ]);

        $responseGenerar = $this->postJson(route("produccion.ordenes.generar"), [
            'fecha' => '2025-11-04',
            'sucursalId' => 'SCZ-001',
            'items' => [
                ['sku' => 'PIZZA-PEP', 'qty' => 1],
                ['sku' => 'PIZZA-MARG', 'qty' => 1]
            ],
        ]);

        $responseGenerar->assertCreated()->assertJsonStructure(['ordenProduccionId']);
        $opId = $responseGenerar->json('ordenProduccionId');

        $this->assertDatabaseHas('orden_produccion', ['id' => $opId, 'estado' => 'CREADA']);

        $this->postJson(route("produccion.ordenes.planificar"), [
            'ordenProduccionId' => $opId,
            'estacionId' => $estacionId,
            'recetaVersionId' => $recetaVersion1Id,
            'porcionId' => $porcionId
        ])->assertCreated()->assertJsonPath('ordenProduccionId', $opId);

        $this->assertDatabaseHas('orden_produccion', ['id' => $opId, 'estado' => 'PLANIFICADA']);

        $this->postJson(route("produccion.ordenes.procesar"), ['ordenProduccionId' => $opId])->assertCreated()->assertJsonPath('ordenProduccionId', $opId);

        $this->assertDatabaseHas('orden_produccion', ['id' => $opId, 'estado' => 'EN_PROCESO']);

        $this->postJson(route("produccion.ordenes.despachar"), [
            'ordenProduccionId' => $opId,
            'itemsDespacho' => [
                ['sku' => 'PIZZA-PEP', 'recetaVersionId' => $recetaVersion2Id],
                ['sku' => 'PIZZA-MARG', 'recetaVersionId' => $recetaVersion1Id]
            ],
            'pacienteId' => $pacienteId,
            'direccionId' => $direccionId,
            'ventanaEntrega' => $ventanaEntregaId,
        ])->assertCreated()->assertJsonPath('ordenProduccionId', $opId);

        $this->assertDatabaseHas('orden_produccion', ['id' => $opId, 'estado' => 'CERRADA']);
        $this->assertDatabaseHas('item_despacho', ['op_id' => $opId]);
    }
}
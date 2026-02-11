<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Produccion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @class FlujoOrdenProduccionIntegrationTest
 * @package Tests\Feature\Produccion
 */
class FlujoOrdenProduccionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_flujo_completo_generar_planificar_procesar_despachar(): void
    {
        $this->seed();

        $estacionId = (string) Str::uuid();
        DB::table('estacion')->insert([
            'id' => $estacionId,
            'nombre' => 'Estación 1', 'created_at' => now(), 'updated_at' => now()
        ]);
        $recetaVersion1Id = (string) Str::uuid();
        DB::table('receta_version')->insert([
            'id' => $recetaVersion1Id,
            'nombre' => 'Pizza Pepperoni v1.0', 'created_at' => now(), 'updated_at' => now()
        ]);
        $recetaVersion2Id = (string) Str::uuid();
        DB::table('receta_version')->insert([
            'id' => $recetaVersion2Id,
            'nombre' => 'Pizza Margarita v2.0', 'created_at' => now(), 'updated_at' => now()
        ]);
        $porcionId = (string) Str::uuid();
        DB::table('porcion')->insert([
            'id' => $porcionId,
            'nombre' => 'Porción estándar', 'peso_gr' => 50, 'created_at' => now(), 'updated_at' => now()
        ]);
        $pacienteId = (string) Str::uuid();
        DB::table('paciente')->insert([
            'id' => $pacienteId,
            'nombre' => 'Paciente Demo', 'created_at' => now(), 'updated_at' => now()
        ]);
        $ventanaEntregaId = (string) Str::uuid();
        DB::table('ventana_entrega')->insert([
            'id' => $ventanaEntregaId,
            'desde' => now(), 'hasta' => now(), 'created_at' => now(), 'updated_at' => now()
        ]);
        $direccionId = (string) Str::uuid();
        DB::table('direccion')->insert([
            'id' => $direccionId,
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
        $suscripcionId = (string) Str::uuid();
        DB::table('suscripcion')->insert([
            'id' => $suscripcionId,
            'nombre' => 'Suscripción Demo', 'created_at' => now(), 'updated_at' => now()
        ]);

        $etiquetaId = (string) Str::uuid();
        DB::table('etiqueta')->insert([
            'id' => $etiquetaId,
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
            'items' => [['sku' => 'PIZZA-PEP', 'qty' => 1], ['sku' => 'PIZZA-MARG', 'qty' => 1]]
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
        $this->assertSame(
            2, DB::table('item_despacho')->where('op_id', $opId)->whereNotNull('paquete_id')->count()
        );
    }
}

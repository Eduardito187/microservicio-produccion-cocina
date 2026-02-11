<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @class PaqueteCrudTest
 * @package Tests\Feature\Maestros
 */
class PaqueteCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_crear_actualizar_y_eliminar_paquete(): void
    {
        $suscripcionId = (string) Str::uuid();
        DB::table('suscripcion')->insert([
            'id' => $suscripcionId,
            'nombre' => 'Suscripcion 1',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $pacienteId = (string) Str::uuid();
        DB::table('paciente')->insert([
            'id' => $pacienteId,
            'nombre' => 'Paciente 1',
            'documento' => 'DOC-1',
            'suscripcion_id' => $suscripcionId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $recetaVersionId = (string) Str::uuid();
        DB::table('receta_version')->insert([
            'id' => $recetaVersionId,
            'nombre' => 'Receta 1',
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $etiquetaId = (string) Str::uuid();
        DB::table('etiqueta')->insert([
            'id' => $etiquetaId,
            'receta_version_id' => $recetaVersionId,
            'suscripcion_id' => $suscripcionId,
            'paciente_id' => $pacienteId,
            'qr_payload' => json_encode(['code' => 'ABC']),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $direccionId = (string) Str::uuid();
        DB::table('direccion')->insert([
            'id' => $direccionId,
            'nombre' => 'Casa',
            'linea1' => 'Calle 1',
            'linea2' => null,
            'ciudad' => 'Ciudad',
            'provincia' => 'Provincia',
            'pais' => 'Pais',
            'geo' => json_encode(['lat' => 1.23, 'lng' => 4.56]),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $ventanaId = (string) Str::uuid();
        DB::table('ventana_entrega')->insert([
            'id' => $ventanaId,
            'desde' => '2026-01-01 08:00:00', 'hasta' => '2026-01-01 12:00:00', 'created_at' => now(), 'updated_at' => now()
        ]);
        $create = $this->postJson(route('paquetes.crear'), [
            'etiquetaId' => $etiquetaId, 'ventanaId' => $ventanaId, 'direccionId' => $direccionId
        ]);

        $create->assertCreated()->assertJsonStructure(['paqueteId']);
        $paqueteId = $create->json('paqueteId');

        $this->getJson(route('paquetes.listar'))
            ->assertOk()->assertJsonFragment(['id' => $paqueteId]);
        $this->getJson(route('paquetes.ver', ['id' => $paqueteId]))
            ->assertOk()->assertJsonFragment(['id' => $paqueteId]);

        $update = $this->putJson(route('paquetes.actualizar', ['id' => $paqueteId]), [
            'etiquetaId' => $etiquetaId, 'ventanaId' => $ventanaId, 'direccionId' => $direccionId
        ]);

        $update->assertOk()->assertJsonPath('paqueteId', $paqueteId);
        $delete = $this->deleteJson(route('paquetes.eliminar', ['id' => $paqueteId]));
        $delete->assertNoContent();
    }
}

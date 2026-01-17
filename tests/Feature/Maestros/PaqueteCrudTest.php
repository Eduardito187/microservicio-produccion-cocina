<?php

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaqueteCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_actualizar_y_eliminar_paquete(): void
    {
        $suscripcionId = DB::table('suscripcion')->insertGetId([
            'nombre' => 'Suscripcion 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pacienteId = DB::table('paciente')->insertGetId([
            'nombre' => 'Paciente 1',
            'documento' => 'DOC-1',
            'suscripcion_id' => $suscripcionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recetaVersionId = DB::table('receta_version')->insertGetId([
            'nombre' => 'Receta 1',
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $etiquetaId = DB::table('etiqueta')->insertGetId([
            'receta_version_id' => $recetaVersionId,
            'suscripcion_id' => $suscripcionId,
            'paciente_id' => $pacienteId,
            'qr_payload' => json_encode(['code' => 'ABC']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $direccionId = DB::table('direccion')->insertGetId([
            'nombre' => 'Casa',
            'linea1' => 'Calle 1',
            'linea2' => null,
            'ciudad' => 'Ciudad',
            'provincia' => 'Provincia',
            'pais' => 'Pais',
            'geo' => json_encode(['lat' => 1.23, 'lng' => 4.56]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ventanaId = DB::table('ventana_entrega')->insertGetId([
            'desde' => '2026-01-01 08:00:00',
            'hasta' => '2026-01-01 12:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $create = $this->postJson(route('paquetes.crear'), [
            'etiquetaId' => $etiquetaId,
            'ventanaId' => $ventanaId,
            'direccionId' => $direccionId,
        ]);

        $create->assertCreated()->assertJsonStructure(['paqueteId']);
        $paqueteId = $create->json('paqueteId');

        $this->getJson(route('paquetes.listar'))
            ->assertOk()
            ->assertJsonFragment(['id' => $paqueteId]);

        $this->getJson(route('paquetes.ver', ['id' => $paqueteId]))
            ->assertOk()
            ->assertJsonFragment(['id' => $paqueteId]);

        $update = $this->putJson(route('paquetes.actualizar', ['id' => $paqueteId]), [
            'etiquetaId' => $etiquetaId,
            'ventanaId' => $ventanaId,
            'direccionId' => $direccionId,
        ]);

        $update->assertOk()->assertJsonPath('paqueteId', $paqueteId);

        $delete = $this->deleteJson(route('paquetes.eliminar', ['id' => $paqueteId]));
        $delete->assertNoContent();
    }
}

<?php

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EtiquetaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_actualizar_y_eliminar_etiqueta(): void
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

        $create = $this->postJson(route('etiquetas.crear'), [
            'recetaVersionId' => $recetaVersionId,
            'suscripcionId' => $suscripcionId,
            'pacienteId' => $pacienteId,
            'qrPayload' => ['code' => 'ABC'],
        ]);

        $create->assertCreated()->assertJsonStructure(['etiquetaId']);
        $etiquetaId = $create->json('etiquetaId');

        $this->getJson(route('etiquetas.listar'))
            ->assertOk()
            ->assertJsonFragment(['id' => $etiquetaId]);

        $this->getJson(route('etiquetas.ver', ['id' => $etiquetaId]))
            ->assertOk()
            ->assertJsonFragment(['id' => $etiquetaId]);

        $update = $this->putJson(route('etiquetas.actualizar', ['id' => $etiquetaId]), [
            'recetaVersionId' => $recetaVersionId,
            'suscripcionId' => $suscripcionId,
            'pacienteId' => $pacienteId,
            'qrPayload' => ['code' => 'DEF'],
        ]);

        $update->assertOk()->assertJsonPath('etiquetaId', $etiquetaId);

        $delete = $this->deleteJson(route('etiquetas.eliminar', ['id' => $etiquetaId]));
        $delete->assertNoContent();
    }
}

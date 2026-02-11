<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @class ActualizarEtiquetaUuidValidationTest
 * @package Tests\Feature\Maestros
 */
class ActualizarEtiquetaUuidValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_actualizar_etiqueta_rechaza_ids_no_uuid(): void
    {
        $etiquetaId = (string) Str::uuid();
        DB::table('etiqueta')->insert([
            'id' => $etiquetaId,
            'qr_payload' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->putJson(route('etiquetas.actualizar', ['id' => $etiquetaId]), [
            'recetaVersionId' => '1',
            'suscripcionId' => '1',
            'pacienteId' => '1',
            'qrPayload' => ['code' => 'X'],
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['recetaVersionId', 'suscripcionId', 'pacienteId']);
    }
}

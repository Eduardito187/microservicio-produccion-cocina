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
 * @class ActualizarPaqueteUuidValidationTest
 * @package Tests\Feature\Maestros
 */
class ActualizarPaqueteUuidValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_actualizar_paquete_rechaza_ids_no_uuid(): void
    {
        $paqueteId = (string) Str::uuid();
        DB::table('paquete')->insert([
            'id' => $paqueteId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->putJson(route('paquetes.actualizar', ['id' => $paqueteId]), [
            'etiquetaId' => '1',
            'ventanaId' => '1',
            'direccionId' => '1',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['etiquetaId', 'ventanaId', 'direccionId']);
    }
}

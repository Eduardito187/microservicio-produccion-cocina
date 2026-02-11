<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @class EtiquetaUuidValidationTest
 * @package Tests\Feature\Maestros
 */
class EtiquetaUuidValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_etiqueta_rechaza_id_no_uuid(): void
    {
        $this->postJson(route('etiquetas.crear'), [
            'recetaVersionId' => '1',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['recetaVersionId']);
    }

    /**
     * @return void
     */
    public function test_etiqueta_uuid_valido_pasa_validacion(): void
    {
        $this->postJson(route('etiquetas.crear'), [
            'recetaVersionId' => (string) Str::uuid(),
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['recetaVersionId']);
    }
}

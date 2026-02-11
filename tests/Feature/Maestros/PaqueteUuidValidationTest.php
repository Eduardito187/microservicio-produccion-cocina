<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @class PaqueteUuidValidationTest
 * @package Tests\Feature\Maestros
 */
class PaqueteUuidValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_paquete_rechaza_id_no_uuid(): void
    {
        $this->postJson(route('paquetes.crear'), [
            'ventanaId' => '1',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['ventanaId']);
    }

    /**
     * @return void
     */
    public function test_paquete_uuid_valido_pasa_validacion(): void
    {
        $this->postJson(route('paquetes.crear'), [
            'ventanaId' => (string) Str::uuid(),
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['ventanaId']);
    }
}

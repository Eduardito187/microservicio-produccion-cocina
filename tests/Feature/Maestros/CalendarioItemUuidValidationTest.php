<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @class CalendarioItemUuidValidationTest
 * @package Tests\Feature\Maestros
 */
class CalendarioItemUuidValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_calendario_item_rechaza_ids_no_uuid(): void
    {
        $this->postJson(route('calendario-items.crear'), [
            'calendarioId' => '1',
            'itemDespachoId' => '1',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['calendarioId', 'itemDespachoId']);
    }

    /**
     * @return void
     */
    public function test_calendario_item_uuid_valido_pasa_validacion(): void
    {
        $this->postJson(route('calendario-items.crear'), [
            'calendarioId' => (string) Str::uuid(),
            'itemDespachoId' => (string) Str::uuid(),
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['calendarioId', 'itemDespachoId']);
    }
}

<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Produccion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class DespacharOPValidationTest
 * @package Tests\Feature\Produccion
 */
class DespacharOPValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_despachar_op_rechaza_items_despacho_vacio(): void
    {
        $response = $this->postJson(route('produccion.ordenes.despachar'), [
            'ordenProduccionId' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'itemsDespacho' => [],
            'pacienteId' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'direccionId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
            'ventanaEntrega' => 'a9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1b3c',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['itemsDespacho']);
    }
}


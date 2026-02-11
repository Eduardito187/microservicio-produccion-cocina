<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Produccion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class GenerarOPValidationTest
 * @package Tests\Feature\Produccion
 */
class GenerarOPValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_generar_op_rechaza_items_invalidos(): void
    {
        $response = $this->postJson(route('produccion.ordenes.generar'), [
            'fecha' => '2025-11-04',
            'sucursalId' => 'SCZ-001',
            'items' => [
                ['sku' => '', 'qty' => 0],
                ['sku' => '   ', 'qty' => 1],
                ['qty' => 1],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'items.0.sku',
            'items.0.qty',
            'items.1.sku',
            'items.2.sku',
        ]);
    }
}

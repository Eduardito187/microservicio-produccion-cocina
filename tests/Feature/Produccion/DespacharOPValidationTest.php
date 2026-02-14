<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Produccion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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
    public function test_ventana_entrega_requiere_uuid(): void
    {
        $payload = [
            'ordenProduccionId' => (string) Str::uuid(),
            'itemsDespacho' => [],
            'pacienteId' => (string) Str::uuid(),
            'direccionId' => (string) Str::uuid(),
            'ventanaEntrega' => '1',
        ];

        $this->postJson(route('produccion.ordenes.despachar'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ventanaEntrega']);
    }

    /**
     * @return void
     */
    public function test_ventana_entrega_uuid_valido_pasa_validacion(): void
    {
        $recetaVersionId = (string) Str::uuid();
        DB::table('receta_version')->insert([
            'id' => $recetaVersionId,
            'nombre' => 'Receta test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'ordenProduccionId' => (string) Str::uuid(),
            'itemsDespacho' => [
                [
                    'sku' => 'PIZZA-PEP',
                    'recetaVersionId' => $recetaVersionId,
                ],
            ],
            'pacienteId' => (string) Str::uuid(),
            'direccionId' => (string) Str::uuid(),
            'ventanaEntrega' => (string) Str::uuid(),
        ];

        $this->postJson(route('produccion.ordenes.despachar'), $payload)
            ->assertStatus(404);
    }
}
